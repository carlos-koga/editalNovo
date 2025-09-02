-- SEQUENCES
CREATE SEQUENCE IF NOT EXISTS public.app_log_id_seq
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 2147483647
    CACHE 1;


-- FUNÇÃO DE ATUALIZAÇÃO DO TS_VECTOR 
CREATE OR REPLACE FUNCTION public.update_edt_aplicacao_tsv()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
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
$BODY$;




-- TABELA DE LOG
-- É um log populado por trigger que chama a função fn_grava_log().
CREATE TABLE IF NOT EXISTS public.app_log (
    id integer NOT NULL DEFAULT nextval('app_log_id_seq'::regclass),
    table_name character varying(50) NOT NULL,
    operation character varying(10) NOT NULL,
    old_data jsonb,
    new_data jsonb,
    changed_at timestamp without time zone DEFAULT LOCALTIMESTAMP,
    changed_by character varying(20) NOT NULL,
    CONSTRAINT app_log_pkey PRIMARY KEY (id)
);
ALTER SEQUENCE public.app_log_id_seq OWNED BY public.app_log.id;


-- TABELA DE ANEXOS
-- Guarda o texto dos anexos que aparecem no edital, após as cláusulas
CREATE TABLE IF NOT EXISTS public.anexo (
    anx_id integer NOT NULL GENERATED ALWAYS AS IDENTITY,
    anx_titulo character varying(130) NOT NULL,
    anx_conteudo text NOT NULL,
    anx_da_criacao timestamp with time zone DEFAULT LOCALTIMESTAMP,
    anx_ativo boolean DEFAULT true,
    CONSTRAINT anexo_pkey PRIMARY KEY (anx_id)
);

-- TABELA DE BANCO DE CLÁUSULAS
-- São as cláusulas modelo que podem ser associadas a um nome de edital
CREATE TABLE IF NOT EXISTS public.banco_clausula (
    cls_nu integer NOT NULL GENERATED ALWAYS AS IDENTITY,
    cls_tx text NOT NULL,
    cls_nu_ordem integer NOT NULL,
    level integer,
    ativo boolean DEFAULT true,
    cls_da_criacao time without time zone DEFAULT CURRENT_TIMESTAMP,
    cls_da_update time without time zone,
    cls_da_inativacao time without time zone,
    cls_obs character varying(300),
    CONSTRAINT banco_clausula_pkey PRIMARY KEY (cls_nu)
);

-- TABELA DE EDITAIS
CREATE TABLE IF NOT EXISTS public.edital (
    edt_id integer NOT NULL GENERATED ALWAYS AS IDENTITY,
    edt_nome character varying(60) NOT NULL,
    edt_versao integer NOT NULL,
    edt_designacao integer NOT NULL,
    edt_bloqueado boolean DEFAULT false,
    edt_doc_aprovacao character varying(50),
    edt_ativo boolean DEFAULT true,
    edt_da_criacao timestamp without time zone DEFAULT LOCALTIMESTAMP,
    edt_da_vigencia date,
    edt_aplicacao jsonb,
    fulltext tsvector,
    CONSTRAINT edital_pkey PRIMARY KEY (edt_id)
);

COMMENT ON TABLE public.edital
    IS 'Nomes de editais';

CREATE INDEX IF NOT EXISTS idx_edt_aplicacao_tsv
    ON public.edital USING gin
    (fulltext);

-- TABELA DE SECTION
-- O nome seria SECAO, mas eu já tinha uma tabela com o mesmo nome.
-- SECAO são as partes do edital que não são cláusulas (logo, identificação da empresa, datas e horários, anexos e apêndices)
CREATE TABLE IF NOT EXISTS public.section (
    sec_id integer NOT NULL GENERATED ALWAYS AS IDENTITY,
    sec_titulo character varying(80) NOT NULL,
    sec_conteudo text NOT NULL,
    sec_ativo boolean NOT NULL DEFAULT true,
    sec_da_criacao time without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT section_pkey PRIMARY KEY (sec_id)
);

-- TABELA DE GRUPO
-- Esta tabela existe no PCON, mas não é exatamente a mesma.
CREATE TABLE IF NOT EXISTS public.grupo (
    grp_nu integer NOT NULL,
    tio_nu integer NOT NULL,
    grp_no character varying(100) NOT NULL,
    grp_in_arte character varying(1),
    grp_in_ativo character varying(1),
    CONSTRAINT grupo_pk PRIMARY KEY (grp_nu)
);

-- TABELA DE MENU
-- Esta tabela existe no PCON, mas não é exatamente a mesma. É o que classifica as tags (tabela submenu)
CREATE TABLE IF NOT EXISTS public.menu (
    menu_id smallint NOT NULL GENERATED ALWAYS AS IDENTITY,
    menu_name smallint NOT NULL,
    menu_text character varying(25) NOT NULL,
    CONSTRAINT menu_pkey PRIMARY KEY (menu_id)
);

-- TABELA DE SUBMENU
-- Esta tabela existe no PCON, mas não é exatamente a mesma. É o que mantém as tags.
CREATE TABLE IF NOT EXISTS public.submenu (
    submenu_id smallint NOT NULL GENERATED ALWAYS AS IDENTITY,
    submenu_text character varying(40) NOT NULL,
    submenu_action character varying(20) NOT NULL,
    menu_id smallint NOT NULL,
    submenu_ordem smallint,
    CONSTRAINT submenu_pkey PRIMARY KEY (submenu_id),
    CONSTRAINT submenu_menu_id_fkey FOREIGN KEY (menu_id)
        REFERENCES public.menu (menu_id)
        ON UPDATE NO ACTION
        ON DELETE CASCADE
);

-- TABELA DE EDITAL_CLAUSULA
CREATE TABLE IF NOT EXISTS public.edital_clausula (
    edt_clausula_id integer NOT NULL GENERATED ALWAYS AS IDENTITY,
    edt_id integer NOT NULL,
    cls_nu integer NOT NULL,
    CONSTRAINT edital_clausula_pkey PRIMARY KEY (edt_clausula_id)
);

COMMENT ON TABLE public.edital_clausula
    IS 'IDs das cláusulas (do banco_clausula) que compõem um nome de edital';

CREATE UNIQUE INDEX IF NOT EXISTS idx_edital_clausula_unique
    ON public.edital_clausula USING btree
    (edt_id ASC NULLS LAST, cls_nu ASC NULLS LAST)
    TABLESPACE pg_default;

-- TABELA DE SECAO
CREATE TABLE IF NOT EXISTS public.secao (
    sec_nu integer NOT NULL GENERATED ALWAYS AS IDENTITY,
    blc_nu integer NOT NULL,
    sec_tx character varying(500) NOT NULL,
    sec_in_ativo character varying(1) NOT NULL DEFAULT 'A',
    sec_dt_inicio date NOT NULL DEFAULT now(),
    sec_dt_fim date,
    sec_nu_ordem integer,
    CONSTRAINT secao_pk PRIMARY KEY (sec_nu)
);

-- TABELA DE EDITAL_SECAO
CREATE TABLE IF NOT EXISTS public.edital_secao (
    edt_id integer NOT NULL,
    sec_id integer NOT NULL,
    sec_ordem integer NOT NULL,
    CONSTRAINT edital_secao_pkey PRIMARY KEY (edt_id, sec_id)
);

-- FUNÇÃO DE VERIFICAÇÃO DE DUPLICIDADE
CREATE OR REPLACE FUNCTION public.fn_check_duplicated_edital()
RETURNS trigger
LANGUAGE 'plpgsql'
COST 100
VOLATILE NOT LEAKPROOF
AS $BODY$
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
$BODY$;

-- FUNÇÃO DE GRAVAÇÃO DE LOG
CREATE OR REPLACE FUNCTION public.fn_grava_log()
RETURNS trigger
LANGUAGE 'plpgsql'
COST 100
VOLATILE NOT LEAKPROOF
AS $BODY$
BEGIN
    INSERT INTO app_log (table_name, operation, old_data, new_data, changed_by)
    VALUES (
        TG_TABLE_NAME,
        TG_OP,
        to_jsonb(OLD),
        to_jsonb(NEW),
        current_setting('application.user', true)
    );
    RETURN NEW;
END;
$BODY$;

-- TRIGGERS
CREATE OR REPLACE TRIGGER trg_log_trigger
    AFTER INSERT OR DELETE OR UPDATE 
    ON public.banco_clausula
    FOR EACH ROW
    EXECUTE FUNCTION public.fn_grava_log();

CREATE OR REPLACE TRIGGER trg_check_duplicated_edital
    BEFORE INSERT OR UPDATE 
    ON public.edital
    FOR EACH ROW
    EXECUTE FUNCTION public.fn_check_duplicated_edital();

CREATE OR REPLACE TRIGGER trg_log_trigger
    AFTER INSERT OR DELETE OR UPDATE 
    ON public.edital
    FOR EACH ROW
    EXECUTE FUNCTION public.fn_grava_log();

CREATE OR REPLACE TRIGGER trg_update_edt_aplicacao_tsv
    BEFORE INSERT OR UPDATE 
    ON public.edital
    FOR EACH ROW
    EXECUTE FUNCTION public.update_edt_aplicacao_tsv();

CREATE OR REPLACE TRIGGER trg_log_trigger
    AFTER INSERT OR DELETE OR UPDATE 
    ON public.edital_clausula
    FOR EACH ROW
    EXECUTE FUNCTION public.fn_grava_log();