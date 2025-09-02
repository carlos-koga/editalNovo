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
-- Data for Name: menu; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.menu OVERRIDING SYSTEM VALUE VALUES (1, 1, 'Dados da contratação');
INSERT INTO public.menu OVERRIDING SYSTEM VALUE VALUES (3, 3, 'Datas e horários');
INSERT INTO public.menu OVERRIDING SYSTEM VALUE VALUES (8, 4, 'Local de entrega');
INSERT INTO public.menu OVERRIDING SYSTEM VALUE VALUES (4, 5, 'Objeto');
INSERT INTO public.menu OVERRIDING SYSTEM VALUE VALUES (2, 2, 'Responsável fase externa');


--
-- Name: menu_menu_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.menu_menu_id_seq', 8, true);


--
-- PostgreSQL database dump complete
--

