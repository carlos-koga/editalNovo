--
-- PostgreSQL database dump
--

-- Dumped from database version 15.1
-- Dumped by pg_dump version 15.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: edital; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (63, 'Clone Aquisição de Capacete', 16, 1, false, 'sdada', false, '2025-07-02 14:30:56.37562', '2025-07-09', '{"grupos": [1], "modalidade": ["XE"], "forma_contratacao": ["SRP"]}', '''1'':3 ''srp'':2 ''xe'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (78, 'teste clonar 28082025', 17, 0, false, '123456', true, '2025-08-28 11:13:27.428135', '2025-08-28', '{"grupos": [52, 7], "modalidade": ["XE"], "forma_contratacao": ["SRP"]}', '''52'':3 ''7'':4 ''srp'':2 ''xe'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (79, 'teste clonagem Edital Capacete', 17, 1, false, '123', true, '2025-09-01 15:04:26.51144', '2025-09-01', '{"grupos": [1], "modalidade": ["XE"], "forma_contratacao": ["SRP"]}', '''1'':3 ''srp'':2 ''xe'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (65, 'Insumo de Impressão SRP', 16, 0, true, 'OFÍCIO CIRCULAR Nº 50924002/2024 - GNOP-DEPEC', true, '2025-07-03 14:28:20.955918', '2025-07-03', '{"grupos": [6], "modalidade": [], "forma_contratacao": ["SRP"]}', '''6'':2 ''srp'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (66, 'Serviço de Confecção de Objetos SRP', 16, 0, false, 'OFÍCIO CIRCULAR Nº 50924002/2024 - GNOP-DEPEC', true, '2025-07-04 11:38:04.11525', '2025-07-04', '{"grupos": [7, 52], "modalidade": [], "forma_contratacao": ["SRP"]}', '''52'':3 ''7'':2 ''srp'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (64, 'Material de Consumo', 16, 0, true, 'OFÍCIO CIRCULAR Nº 50924002/2024 - GNOP-DEPEC', true, '2025-07-02 14:59:39.694047', '2025-07-02', '{"grupos": [3, 4], "modalidade": ["XE"], "forma_contratacao": ["SRP"]}', '''3'':3 ''4'':4 ''srp'':2 ''xe'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (73, 'teste', 16, 0, false, '1234', true, '2025-08-26 16:33:50.962722', '2025-08-26', '{"grupos": [1], "modalidade": ["XE"], "forma_contratacao": ["SRP"]}', '''1'':3 ''srp'':2 ''xe'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (67, 'Clone de capacete', 16, 7, false, 'teste de duplicação', true, '2025-07-04 15:05:22.468208', '2025-07-10', '{"grupos": [2], "modalidade": ["XE"], "forma_contratacao": ["SRP"]}', '''2'':3 ''srp'':2 ''xe'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (72, 'EIS Tratamento', 16, 8, false, 'NJ/GCON-DEJUR/SEI-58077251/2025', true, '2025-07-17 09:58:13.216493', '2025-06-01', '{"grupos": [107], "modalidade": ["XE"], "forma_contratacao": ["SRP"]}', '''107'':3 ''srp'':2 ''xe'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (13, 'Aquisição de Capacete', 16, 0, true, 'NJ/GCON-DEJUR/SEI-41616756/2023', true, '2025-06-06 15:00:13.394773', '2025-05-01', '{"grupos": [1], "modalidade": ["XE"], "forma_contratacao": ["SRP"]}', '''1'':3 ''srp'':2 ''xe'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (76, 'teste', 16, 0, false, '1234', true, '2025-08-26 17:04:46.841868', '2025-08-26', '{"grupos": [1], "modalidade": ["XE"], "forma_contratacao": ["SRP"]}', '''1'':3 ''srp'':2 ''xe'':1');
INSERT INTO public.edital OVERRIDING SYSTEM VALUE VALUES (43, 'Edital de teste do Koga', 4, 4, false, '', false, '2025-06-06 15:00:13.394773', '2025-06-06', '{"grupos": [1], "modalidade": ["DLE", "DL", "INEX", "LCA", "LCF", "XE"], "forma_contratacao": ["SRP", "C"]}', '''1'':9 ''c'':8 ''dl'':2 ''dle'':1 ''inex'':3 ''lca'':4 ''lcf'':5 ''srp'':7 ''xe'':6');


--
-- Name: edital_edt_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.edital_edt_id_seq', 79, true);


--
-- PostgreSQL database dump complete
--

