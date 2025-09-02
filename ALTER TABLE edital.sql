ALTER TABLE edital
  ADD COLUMN fulltext tsvector;

CREATE OR REPLACE FUNCTION update_edt_aplicacao_tsv() 
RETURNS trigger AS $$
BEGIN
  NEW.fulltext :=
    to_tsvector(
      'simple',
      lower(
        concat_ws(' ',
          NEW.edt_aplicacao->>'modalidade',
          NEW.edt_aplicacao->>'forma_contratacao',
          NEW.edt_aplicacao->>'grupos'
        )
      )
    );
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_update_edt_aplicacao_tsv
BEFORE INSERT OR UPDATE ON edital
FOR EACH ROW
EXECUTE FUNCTION update_edt_aplicacao_tsv();


CREATE INDEX idx_edt_aplicacao_tsv ON edital USING GIN (edt_aplicacao_tsv);



SELECT *
FROM edital 
WHERE to_tsvector(
        'portuguese',
        concat_ws(' ',
          edt_aplicacao->>'modalidade', 
          edt_aplicacao->>'forma_contratacao', 
          edt_aplicacao->>'grupos'
        )
      ) @@ to_tsquery('portuguese', 'filtro dinâmico')
  AND edt_ativo = true;



CREATE OR REPLACE FUNCTION fn_check_duplicated_edital()
RETURNS trigger AS
$$
DECLARE
    tsquery_modalidade text;
    tsquery_forma      text;
    tsquery_grupos     text;
    tsquery_full       text;
    v_count            int;
    j_modalidade       jsonb;
    j_forma            jsonb;
    j_grupos           jsonb;
BEGIN
    ----------------------------------------------------------------------------
    -- 0) Se não for ativo OU se edt_aplicacao for NULL, sai sem checar
    ----------------------------------------------------------------------------
    IF NEW.edt_ativo IS DISTINCT FROM TRUE
       OR NEW.edt_aplicacao IS NULL THEN
        RETURN NEW;
    END IF;

    ----------------------------------------------------------------------------
    -- 1) Normaliza cada campo para array JSONB
    ----------------------------------------------------------------------------
    j_modalidade :=
      CASE
        WHEN jsonb_typeof(NEW.edt_aplicacao->'modalidade') = 'array'
          THEN NEW.edt_aplicacao->'modalidade'
        WHEN NEW.edt_aplicacao->>'modalidade' IS NOT NULL
          THEN jsonb_build_array(NEW.edt_aplicacao->'modalidade')
        ELSE '[]'::jsonb
      END;

    j_forma :=
      CASE
        WHEN jsonb_typeof(NEW.edt_aplicacao->'forma_contratacao') = 'array'
          THEN NEW.edt_aplicacao->'forma_contratacao'
        WHEN NEW.edt_aplicacao->>'forma_contratacao' IS NOT NULL
          THEN jsonb_build_array(NEW.edt_aplicacao->'forma_contratacao')
        ELSE '[]'::jsonb
      END;

    j_grupos :=
      CASE
        WHEN jsonb_typeof(NEW.edt_aplicacao->'grupos') = 'array'
          THEN NEW.edt_aplicacao->'grupos'
        WHEN NEW.edt_aplicacao->>'grupos' IS NOT NULL
          THEN jsonb_build_array(NEW.edt_aplicacao->'grupos')
        ELSE '[]'::jsonb
      END;

    ----------------------------------------------------------------------------
    -- 2) Monta as tsqueries parciais
    ----------------------------------------------------------------------------
    SELECT string_agg(lower(elem), '|')
      INTO tsquery_modalidade
      FROM jsonb_array_elements_text(j_modalidade) AS t(elem);

    SELECT string_agg(lower(elem), '|')
      INTO tsquery_forma
      FROM jsonb_array_elements_text(j_forma) AS t(elem);

    SELECT string_agg(elem, '|')
      INTO tsquery_grupos
      FROM jsonb_array_elements_text(j_grupos) AS t(elem);

    tsquery_full := format('(%s) & (%s) & (%s)',
                           COALESCE(tsquery_modalidade, ''),
                           COALESCE(tsquery_forma,      ''),
                           COALESCE(tsquery_grupos,     ''));

    ----------------------------------------------------------------------------
    -- 3) Verifica duplicata entre EDITAIS ATIVOS
    ----------------------------------------------------------------------------
    SELECT COUNT(*) INTO v_count
      FROM edital
     WHERE edt_ativo = TRUE
       AND (NEW.edt_id IS NULL OR edt_id <> NEW.edt_id)
       AND to_tsvector(
             'portuguese',
             concat_ws(' ',
               edt_aplicacao->>'modalidade',
               edt_aplicacao->>'forma_contratacao',
               edt_aplicacao->>'grupos')
           )
        @@ to_tsquery('portuguese', tsquery_full);

    IF v_count > 0 THEN
        RAISE EXCEPTION
          'Combinação duplicada entre editais ativos: %', tsquery_full;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_check_duplicated_edital ON edital;

CREATE TRIGGER trg_check_duplicated_edital
BEFORE INSERT OR UPDATE ON edital
FOR EACH ROW
EXECUTE FUNCTION fn_check_duplicated_edital();




INSERT INTO carrinho_json (crr_nu_carrinho, json_data) VALUES(
2500002,
 '{
    "modalidade_edital": {
        "modalidade": "DLE",
        "forma_contratacao": "C",
        "grupos": 1,
        "modalidade_tipo": "Dispensa de Licitação por Valor Eletrônica"
    },
    "carrinho": 2500002,
    "modalidade_numero": 3434,
    "modalidade_ano": 2024,
    "objeto": "A presente licitação tem como objeto a aquisição de [Capacete para Ocupantes de Motocicletas - viseira manual - modelo 2 - Tamanhos 56, 58, 60, 62 e 64] , discriminados no quadro abaixo, conforme Especificação Técnica e demais condições deste Edital e seus Anexos.",
    "objeto_sucinto": "Aquisição de capacete",
    "criterio_julgamento": "Menor preço",
    "tipo_objeto": "Aquisição",
    "arte": "não",
    "regra_tributaria": "ICMS, observando-se as regras de diferencial de alíquota e substituição tributária.",
    "lote": "<table border=\"1\"><thead><tr><th>Coluna 1</th><th>Koga</th></tr></thead><tbody><tr><td>Valor 1000</td><td>Valor 3434</td></tr><tr><td>Valor 3</td><td>Valor 4</td></tr></tbody></table>",
    "pauta": "<table border=\"1\" cellspacing=\"0\" cellpadding=\"4\"><thead><tr><th>Ordem</th><th>Responsável</th><th>Departamento</th><th>Data de Distribuição</th><th>Observações</th></tr></thead><tbody><tr><td>1</td><td>João Silva</td><td>Financeiro</td><td>2025-06-10</td><td>Aguardando aprovação</td></tr><tr><td>2</td><td>Maria Oliveira</td><td>Recursos Humanos</td><td>2025-06-11</td><td>Em processamento</td></tr><tr><td>3</td><td>Pedro Santos</td><td>Tecnologia da Informação</td><td>2025-06-12</td><td>Finalizado</td></tr></tbody></table>"
}'
)


update carrinho_json set json_data = '{
    "modalidade_edital": {
        "modalidade": "XE",
        "forma_contratacao": "SRP",
        "grupos": 1,
        "modalidade_tipo": "Pregão Eletrônico"
    },
    "carrinho": 2500001,
    "modalidade_numero": 12345,
    "modalidade_ano": 2025,
    "objeto": "A presente licitação tem como objeto a aquisição de [Capacete para Ocupantes de Motocicletas - viseira manual - modelo 2 - Tamanhos 56, 58, 60, 62 e 64] , discriminados no quadro abaixo, conforme Especificação Técnica e demais condições deste Edital e seus Anexos.",
    "objeto_sucinto": "Aquisição de capacete",
    "criterio_julgamento": "Menor preço",
    "tipo_objeto": "Aquisição",
    "arte": "não",
    "regra_tributaria": "ICMS, observando-se as regras de diferencial de alíquota e substituição tributária.",
    "lote": "<table border=\"1\"><thead><tr><th>Coluna 1</th><th>Coluna 2</th></tr></thead><tbody><tr><td>Valor 1</td><td>Valor 2</td></tr><tr><td>Valor 3</td><td>Valor 4</td></tr></tbody></table>",
    "pauta": "<table border=\"1\" cellspacing=\"0\" cellpadding=\"4\"><thead><tr><th>Ordem</th><th>Responsável</th><th>Departamento</th><th>Data de Distribuição</th><th>Observações</th></tr></thead><tbody><tr><td>1</td><td>João Silva</td><td>Financeiro</td><td>2025-06-10</td><td>Aguardando aprovação</td></tr><tr><td>2</td><td>Maria Oliveira</td><td>Recursos Humanos</td><td>2025-06-11</td><td>Em processamento</td></tr><tr><td>3</td><td>Pedro Santos</td><td>Tecnologia da Informação</td><td>2025-06-12</td><td>Finalizado</td></tr></tbody></table>"
}'

