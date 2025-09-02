<?php
require('../vendor/autoload.php');

use App\Database\Connect; // SQL Server

$con =  new Connect();
if (empty($con->getErrorMessage())) {

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
   COALESCE(c.qtd_clausulas,0) AS qtd_clausulas,

   /* -------- modalidade -------- */
   (
     SELECT string_agg(elem,'<br>')
     FROM jsonb_array_elements_text(
            CASE
              WHEN jsonb_typeof(e.edt_aplicacao->'modalidade')='array'
                   THEN e.edt_aplicacao->'modalidade'
              WHEN jsonb_exists(e.edt_aplicacao,'modalidade')
                   THEN jsonb_build_array(e.edt_aplicacao->'modalidade')
              ELSE '[]'::jsonb
            END ) AS t(elem)
   ) AS modalidade,

   /* -------- forma_contratacao -------- */
   (
     SELECT string_agg(elem,'<br>')
     FROM jsonb_array_elements_text(
            CASE
              WHEN jsonb_typeof(e.edt_aplicacao->'forma_contratacao')='array'
                   THEN e.edt_aplicacao->'forma_contratacao'
              WHEN jsonb_exists(e.edt_aplicacao,'forma_contratacao')
                   THEN jsonb_build_array(e.edt_aplicacao->'forma_contratacao')
              ELSE '[]'::jsonb
            END ) AS t(elem)
   ) AS forma_contratacao,

   /* -------- grupos -------- */
   (
     SELECT '<ul class=\"classGrupos\">'||
            string_agg('<li>'||grp.grp_no||'</li>','')||
            '</ul>'
     FROM jsonb_array_elements_text(
            CASE
              WHEN jsonb_typeof(e.edt_aplicacao->'grupos')='array'
                   THEN e.edt_aplicacao->'grupos'
              WHEN jsonb_exists(e.edt_aplicacao,'grupos')
                   THEN jsonb_build_array(e.edt_aplicacao->'grupos')
              ELSE '[]'::jsonb
            END) AS t(elem)
     JOIN grupo grp ON t.elem::int = grp.grp_nu
   ) AS grupos

FROM edital e
LEFT JOIN (
   SELECT edt_id, COUNT(*) AS qtd_clausulas
   FROM   edital_clausula
   GROUP  BY edt_id
) c ON c.edt_id = e.edt_id;";
            
   $stmt = $con->pdo->query($sql);          // não use prepare(), pois não há placeholders
   
   echo '<div style="float:right">';
   echo 'Pesquisar <input id="custom-filter" class="form-control" placeholder="Buscar registros" type="search"/>';
   echo '</div>';
   echo '<table id="tableDados"  class="stripe">';
   echo '<thead>';
   echo '<tr>';
   echo '    <th class="nosort notexport"></th>';
   echo '    <th>ID</th>';
   echo '    <th>Nome</th>';
   echo '    <th title="Modalidade">Mod.</th>';
   echo '    <th title="Forma de contratação">Forma</th>';
   echo '    <th>Versão</th>';
   echo '    <th title="Designação da versão" >Des.</th>';
   echo '    <th title="Edital bloqueado para alterações" class="check">Bloq.</th>';
   echo '    <th class="check">Ativo</th>';
   echo '    <th>Doc aprovação</th>';
   echo '    <th>Grupos</th>';
   echo '    <th class="DDMMYYYY">Vigência</th>';
   echo '    <th class="DDMMYYYY">Criado em</th>';    
   echo '    <th>Qtd. cláus.</th>';
   echo '</tr>';
   echo '</thead>';
   echo '<tfoot>';
   echo '<tr>';    
   echo '    <th></th>';
   echo '    <th class="columnFilter" size="2">ID</th>';
   echo '    <th class="columnFilter" size="30">Nome</th>';
   echo '    <th class="columnFilter" size="8">Mod;</th>';
   echo '    <th class="columnFilter" size="8">Forma</th>';
   echo '    <th class="columnFilter" size="3">Versão</th>';
   echo '    <th class="columnFilter" size="3">Des.</th>';
   echo '    <th class="checkboxFilter" size="4">Bloq.</th>';
   echo '    <th class="checkboxFilter" size="4">Ativo</th>';
   echo '    <th class="columnFilter" size="15">Doc aprovação</th>';
   echo '    <th class="columnFilter" size="15">Grupos</th>';
   echo '    <th class="columnFilter" size="10">Vigência</th>';
   echo '    <th class="columnFilter" size="10">Criado em</th>';
   echo '    <th class="columnFilter" size="3">Qtd. cláus.</th>';
   echo '</tr>';
   echo '</tfoot>';
   echo '<tbody>';
   
   while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

      echo '<tr id="' . $row['edt_id'] . '">';  
      echo '<td>';
      echo '<img src="eye.png" alt="" class="view classPointer" title="Visualizar edital" />';
      echo '&nbsp;';
      echo '<img src="edit.webp" alt="" class="edit classPointer" title="Editar edital" />';
      echo '&nbsp;<img src="clonar.png" alt="" '; 
      if ( $row['qtd_clausulas']  == 0 ) 
         echo ' class="img-desabilitada" title="Não há cláusulas para clonar" ';
      else 
         echo ' class="clonar classPointer" title="Clonar edital" ';
      echo '/>';
      echo '&nbsp;<img src="link.png" alt="" class="link classPointer" title="Associar seções" />';
      //echo '&nbsp;<img src="rules.png" alt="" class="rules classPointer" title="Regras de aplicação" />';
      
      echo '</td>';          
      echo '<td>' . $row['edt_id'] . '</td>';
      echo '<td>' . $row['edt_nome'] . '</td>';
      echo '<td>' . $row['modalidade'] . '</td>';
      echo '<td>' . $row['forma_contratacao'] . '</td>';
      echo '<td>' . $row['edt_versao'] . '</td>';
      echo '<td>' . $row['edt_designacao'] . '</td>';
      echo '<td style="text-align:center">';
      /*if ( $row['edt_bloqueado'] ) 
         echo '<img src="../resources/img/lock.gif" data-status="S" alt="S">';            
      else
         echo '<img src="../resources/img/blank.gif" data-status="N" alt="N">';            */

      echo '<label class="switch sm">';
      echo '<input type="checkbox" id="chkBloqueado" name="chkBloqueado" class="toggle"';
      if ( $row['edt_bloqueado'] )  
         echo ' checked="checked "';
      
      echo '     ><span class="slider round"></span>';
      echo '</label>'; 
      echo '</td>';



      echo '<td style="text-align:center">';
      
      echo '<label class="switch sm">';
      echo '<input type="checkbox" id="chkAtivo" name="chkAtivo" class="toggle"';
      if ( $row['edt_ativo'] )  
         echo ' checked="checked "';

      echo '     ><span class="slider round"></span>';
      echo '</label>'; 
      echo '</td>';
      echo '<td>' . $row['edt_doc_aprovacao'] . '</td>';
      echo '<td>' . $row['grupos'] . '</td>';
      echo '<td>' . (!empty($row['edt_da_vigencia']) ? date('d/m/Y', strtotime($row['edt_da_vigencia'])) : 'N/A') . '</td>';
      echo '<td>' . date('d/m/Y H:i:s', strtotime($row['edt_da_criacao'])) . '</td>';
      echo '<td style="text-align:right">' . $row['qtd_clausulas'] . '</td>';
      echo '</tr>';
   }
   
   echo '</tbody>';
   echo '</table>';

} else {
    echo $con->getErrorMessage();
}

unset($con);




