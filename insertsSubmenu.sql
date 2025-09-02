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
-- Data for Name: submenu; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (7, 'Órgão licitante', '«local_licitacao»', 2, 10);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (8, 'Endereço', '«endereco_licitacao»', 2, 20);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (9, 'Telefone', '«telefone_licitacao»', 2, 30);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (10, 'e-mail', '«email_licitacao»', 2, 40);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (11, 'Início do acolhimento das propostas', '«data_hora_inicio»', 3, 10);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (12, 'Data de Abertura das Proposta', '«data_hora_abertura»', 3, 20);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (13, 'Data do Pregão e horário da Disputa', '«data_hora_disputa»', 3, 30);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (15, 'Texto descritivo do objeto', '«objeto_descricao»', 4, 10);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (16, 'Lotes', '«lote»', 4, 20);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (14, 'Intervalo mínimo entre os lances', '«intervalo_lances»', 3, 40);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (20, 'Endereço de entrega', '«endereco_entrega»', 8, 1);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (21, 'Telefone', '«telefone_entrega»', 8, 2);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (22, 'Pessoa de contato', '«contato_entrega»', 8, 3);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (24, 'Tag de teste', '«teste»', 8, 4);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (25, 'Prazo em dias', '«dias»', 3, 41);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (26, 'Data limite', '«data_limite»', 3, 42);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (1, 'Modalidade', '«modalidade»', 1, 1);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (2, 'Número do edital', '«numero_edital»', 1, 2);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (3, 'Objeto', '«objeto»', 1, 4);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (29, 'Ano do edital', '«ano_edital»', 1, 3);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (6, 'Tipo', '«tipo»', 1, 6);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (30, 'Objeto sucinto', '«objeto_sucinto»', 1, 5);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (4, 'ICMS', '«icms»', 1, 7);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (28, 'Dados Contratação', '«Dados_Contratacao»', 1, 9);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (5, 'Forma de contratação', '«forma_contratacao»', 1, 8);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (27, 'Pregoeiro', '«nome_pregoeiro»', 2, 41);
INSERT INTO public.submenu OVERRIDING SYSTEM VALUE VALUES (31, 'Portaria do pregoeiro', '«prt_pregoeiro»', 2, 42);


--
-- Name: submenu_submenu_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.submenu_submenu_id_seq', 31, true);


--
-- PostgreSQL database dump complete
--

