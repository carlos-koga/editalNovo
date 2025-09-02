


CREATE TABLE app_log (
    id SERIAL PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    operation VARCHAR(10) NOT NULL,
    old_data JSONB,
    new_data JSONB,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changed_by VARCHAR(20) NOT NULL
);


CREATE OR REPLACE FUNCTION fn_grava_log()
RETURNS TRIGGER AS $$
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
$$ LANGUAGE plpgsql;


CREATE TRIGGER trg_log_trigger
AFTER INSERT OR UPDATE OR DELETE ON customers
FOR EACH ROW
EXECUTE FUNCTION fn_grava_log();


SET application.user = 'carlos';


SELECT DISTINCT tg.tgrelid::regclass AS tabela
FROM pg_trigger tg
WHERE tg.tgname LIKE '%log%';


COMMENT ON TABLE banco_clausula IS 'Armazenamento de todas cláusulas de edital';
COMMENT ON TABLE edital IS 'Nomes de editais';
COMMENT ON TABLE edital_clausula IS 'IDs das cláusulas (do banco_clausula) que compõem um nome de edital';


SELECT 
    c.relname AS tabela, 
    d.description AS comentario
FROM 
    pg_class c
LEFT JOIN 
    pg_description d ON d.objoid = c.oid
WHERE 
    c.relkind = 'r'
AND 
    c.oid IN (
        SELECT DISTINCT tg.tgrelid
        FROM pg_trigger tg
        WHERE tg.tgname = 'trg_log_trigger'
    )
    ORDER BY c.relname;

SELECT json_agg(
    json_build_object(
        'value', c.relname,
        'text', concat(c.relname, ' - ', coalesce(d.description, 'Sem comentário'))
    )
) AS tabelas
FROM pg_class c
LEFT JOIN pg_description d ON d.objoid = c.oid
WHERE c.relkind = 'r'
AND c.oid IN (
    SELECT DISTINCT tg.tgrelid
    FROM pg_trigger tg
    WHERE tg.tgname = 'trg_log_trigger'
);

SELECT pg_get_serial_sequence('banco_clausula', 'cls_nu');
$clsNu = $con->pdo->lastInsertId("banco_clausula_cls_nu_seq"); 

