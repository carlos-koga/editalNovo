<?php
require('../vendor/autoload.php');

use App\Database\Connect; 

header('Content-Type: application/json');

$oper = htmlspecialchars($_POST['txtOper'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtNome'])) 
    $strNome = htmlspecialchars($_POST['txtNome'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtVersao'])) 
   $intVersao = htmlspecialchars($_POST['txtVersao'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtDesignacao'])) 
    $intDesignacao = htmlspecialchars($_POST['txtDesignacao'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtDoc'])) 
    $strDoc = htmlspecialchars($_POST['txtDoc'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtID'])) 
   $intId =  htmlspecialchars($_POST['txtID'], ENT_QUOTES, 'UTF-8'); 

if ( $oper != 'ed' )   
   $intId = 0;

if ( isset($_POST['txtVigencia'])) 
   $vigencia = htmlspecialchars($_POST['txtVigencia'], ENT_QUOTES, 'UTF-8'); 
   
$modalidade        = isset($_POST['chkModalidade']) ? $_POST['chkModalidade'] : [];
$formaContratacao  = isset($_POST['chkForma']) ? $_POST['chkForma'] : [];
$grupos            = isset($_POST['grupos']) ? $_POST['grupos'] : [];
// converte cada elemento para int
$grupos = array_map('intval', $grupos);

   // Monta o JSON que será armazenado na coluna JSONB
$jsonToStore = json_encode(
    [
        'modalidade'        => $modalidade,
        'forma_contratacao' => $formaContratacao,
        'grupos'            => $grupos
    ],
    JSON_UNESCAPED_UNICODE
);



$con =  new Connect();
if (empty($con->getErrorMessage())) {

    $parteModalidade = buildTsOr($modalidade);          // ex.: "(dle|xe)"
    $parteForma      = buildTsOr($formaContratacao);               // ex.: "(srp|c)"
    $parteGrupos     = buildTsOr($grupos, false);         // ex.: "(1|4|7)"


    /*************************************************************
     * 3) Junta com “ & ” ignorando nulos
     *************************************************************/
    $tsParts = array_filter([$parteModalidade, $parteForma, $parteGrupos]);
    $tsq     = $tsParts ? implode(' & ', $tsParts) : null;       // "(dle|xe) & (srp|c) & (1|4|7)"


    /* =======================================================================
DUPLICATES CHECK + “desdobramento” de edt_aplicacao em colunas legíveis
-----------------------------------------------------------------------
:tsq → string gerada no PHP, ex.: '(dle|xe) & (srp|c) & (1|4|7)'
      (se vier vazia/null, o filtro full-text é ignorado)
======================================================================= */

    $sql = "SELECT
    e.edt_id,
    e.edt_nome,
    e.edt_versao,
    e.edt_designacao,
    e.edt_bloqueado,
    e.edt_doc_aprovacao,
    e.edt_da_criacao,
    e.edt_da_vigencia,
    e.edt_ativo,
    /* colunas derivadas do JSONB */
    a.modalidade,
    a.forma_contratacao,
    a.grupos
    FROM edital AS e
    /* -------------------------------------------------
    LATERAL: explode edt_aplicacao → 3 colunas
    ------------------------------------------------- */
    LEFT JOIN LATERAL (
    WITH
        j_modalidade AS (
        SELECT CASE
                    WHEN jsonb_typeof(e.edt_aplicacao->'modalidade') = 'array'
                        THEN e.edt_aplicacao->'modalidade'
                    WHEN jsonb_exists(e.edt_aplicacao, 'modalidade')
                        THEN jsonb_build_array(e.edt_aplicacao->'modalidade')
                    ELSE '[]'::jsonb
                END AS j
        ),
        j_forma AS (
        SELECT CASE
                    WHEN jsonb_typeof(e.edt_aplicacao->'forma_contratacao') = 'array'
                        THEN e.edt_aplicacao->'forma_contratacao'
                    WHEN jsonb_exists(e.edt_aplicacao, 'forma_contratacao')
                        THEN jsonb_build_array(e.edt_aplicacao->'forma_contratacao')
                    ELSE '[]'::jsonb
                END AS j
        ),
        j_grupos AS (
        SELECT CASE
                    WHEN jsonb_typeof(e.edt_aplicacao->'grupos') = 'array'
                        THEN e.edt_aplicacao->'grupos'
                    WHEN jsonb_exists(e.edt_aplicacao, 'grupos')
                        THEN jsonb_build_array(e.edt_aplicacao->'grupos')
                    ELSE '[]'::jsonb
                END AS j
        )

    SELECT
        /* Modalidade → “dle<br>xe” */
        (SELECT string_agg(elem, '<br>')
            FROM jsonb_array_elements_text((SELECT j FROM j_modalidade)) t(elem)
        ) AS modalidade,

        /* Forma de contratação → “srp<br>c” */
        (SELECT string_agg(elem, '<br>')
            FROM jsonb_array_elements_text((SELECT j FROM j_forma)) t(elem)
        ) AS forma_contratacao,

        /* Grupos → lista não-numerada */
        (SELECT '<ul>' ||
                string_agg('<li>' || grp.grp_no || '</li>', '') ||
                '</ul>'
            FROM jsonb_array_elements_text((SELECT j FROM j_grupos)) t(elem)
            JOIN grupo grp ON t.elem::int = grp.grp_nu
        ) AS grupos
    ) a ON TRUE  -- lateral sempre casa

    /* -------------------------------------------------
    Filtro: só editais ativos e, se houver, tsquery dinâmica
    ------------------------------------------------- */
    WHERE e.edt_ativo = TRUE AND edt_id <> CAST(:editalId AS integer)
    AND (
        CAST(:tsq AS text) IS NULL OR CAST(:tsq AS text) = ''
        OR to_tsvector(
                'portuguese',
                concat_ws(' ',
                e.edt_aplicacao->>'modalidade',
                e.edt_aplicacao->>'forma_contratacao',
                e.edt_aplicacao->>'grupos'
                )
            ) @@ to_tsquery('portuguese', CAST(:tsq AS text))
        );";

    $stmt = $con->pdo->prepare($sql);
    $stmt->bindValue(':tsq', $tsq ?? null, is_null($tsq) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':editalId', $intId, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(
        $rows,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
} else {
    echo json_encode([
        "erro" => 1,
        "msg" => 'Erro de conexão com o banco de dados: ' . $con->getErrorMessage()
    ]);        
    
}

unset($con);


/*************************************************************/
function buildTsOr(array $vals, bool $toInt = false): ?string
{
    if ($toInt) {
        $vals = array_map('intval', $vals);
    } else {
        /*  letras/números/_  → mantém
            acentos e espaço  → remove, depois lowercase          */
        $vals = array_map(
            fn($v) => preg_replace('/[^\p{L}\p{N}_]+/u', '', mb_strtolower($v)),
            $vals
        );
    }

    /* remove vazios/duplicados */
    $vals = array_values(array_unique(array_filter($vals, fn($v) => $v !== '')));

    return $vals ? '(' . implode('|', $vals) . ')' : null;   // "(a|b|c)"
}
