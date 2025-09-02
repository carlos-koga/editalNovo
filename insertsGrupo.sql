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
-- Data for Name: grupo; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.grupo VALUES (1, 1, 'capacete motociclista', 'N', 'A');
INSERT INTO public.grupo VALUES (2, 1, 'uniforme', 'N', 'A');
INSERT INTO public.grupo VALUES (3, 1, 'insumo administrativo', 'N', 'A');
INSERT INTO public.grupo VALUES (4, 1, 'insumo operacional', 'N', 'A');
INSERT INTO public.grupo VALUES (5, 1, 'produto comercializável com arte', 'S', 'A');
INSERT INTO public.grupo VALUES (6, 1, 'insumos de impressão', 'N', 'A');
INSERT INTO public.grupo VALUES (7, 2, 'objeto personalizado', 'S', 'A');
INSERT INTO public.grupo VALUES (8, 1, 'bens móveis', 'N', 'A');
INSERT INTO public.grupo VALUES (10, 1, 'bicicleta', 'N', 'A');
INSERT INTO public.grupo VALUES (11, 1, 'equipamentos', 'N', 'A');
INSERT INTO public.grupo VALUES (12, 1, 'insumo de comunicação e evento', 'N', 'A');
INSERT INTO public.grupo VALUES (13, 1, 'água potável em caminhão pipa por demanda', 'N', 'A');
INSERT INTO public.grupo VALUES (14, 1, 'equipamentos de informática e automação', 'N', 'A');
INSERT INTO public.grupo VALUES (15, 1, 'equipamentos de microinformática', 'N', 'A');
INSERT INTO public.grupo VALUES (16, 1, 'equipamentos de rede, produção ou software', 'N', 'A');
INSERT INTO public.grupo VALUES (17, 1, 'gás GLP', 'N', 'A');
INSERT INTO public.grupo VALUES (18, 1, 'sistema de triagem automático de encomendas', 'N', 'A');
INSERT INTO public.grupo VALUES (19, 1, 'unitizadores', 'N', 'A');
INSERT INTO public.grupo VALUES (20, 1, 'veículo caminhão', 'N', 'A');
INSERT INTO public.grupo VALUES (21, 1, 'veículo furgão', 'N', 'A');
INSERT INTO public.grupo VALUES (22, 1, 'veículo motocicleta', 'N', 'A');
INSERT INTO public.grupo VALUES (23, 1, 'circuito fechado de televisão (CFTV), incluindo instalação', 'N', 'A');
INSERT INTO public.grupo VALUES (25, 1, 'circuito fechado de televisão (CFTV), incluindo instalação e projeto', 'N', 'A');
INSERT INTO public.grupo VALUES (26, 1, 'cofre com fechadura eletrônica de retardo, incluindo instalação', 'N', 'A');
INSERT INTO public.grupo VALUES (27, 1, 'equipamentos climatizadores, incluindo instalação', 'N', 'A');
INSERT INTO public.grupo VALUES (28, 1, 'kit vídeo porteiro eletrônico, incluindo instalação', 'N', 'A');
INSERT INTO public.grupo VALUES (29, 1, 'máquina paletizadora, incluindo instalação', 'N', 'A');
INSERT INTO public.grupo VALUES (30, 1, 'plataformas niveladoras de doca manual mecânica, incluindo instalação', 'N', 'A');
INSERT INTO public.grupo VALUES (31, 1, 'porta com detector de metais, incluindo instalação', 'N', 'A');
INSERT INTO public.grupo VALUES (33, 1, 'sistema de alarme, incluindo instalação', 'N', 'A');
INSERT INTO public.grupo VALUES (34, 1, 'produto comercializável sem arte', 'N', 'A');
INSERT INTO public.grupo VALUES (35, 6, 'acesso internet móvel', 'N', 'A');
INSERT INTO public.grupo VALUES (36, 6, 'agenciamento de benefícios', 'N', 'A');
INSERT INTO public.grupo VALUES (37, 6, 'agenciamento de transporte de carga', 'N', 'A');
INSERT INTO public.grupo VALUES (38, 6, 'assessoria técnica', 'N', 'A');
INSERT INTO public.grupo VALUES (39, 7, 'assistência farmacêutica', 'N', 'A');
INSERT INTO public.grupo VALUES (40, 6, 'bombeiro civil', 'N', 'A');
INSERT INTO public.grupo VALUES (41, 7, 'brigadista', 'N', 'A');
INSERT INTO public.grupo VALUES (42, 9, 'carga de logística integrada por meio rodoviário', 'N', 'A');
INSERT INTO public.grupo VALUES (43, 9, 'carga FNDE, modalidade viagem extra', 'N', 'A');
INSERT INTO public.grupo VALUES (44, 9, 'carga FNDE, modalidade viagem regular', 'N', 'A');
INSERT INTO public.grupo VALUES (45, 9, 'carga por meio aéreo para a Rede Postal Noturna (RPN)', 'N', 'A');
INSERT INTO public.grupo VALUES (46, 9, 'carga por meio multimodal, na modalidade regional', 'N', 'A');
INSERT INTO public.grupo VALUES (47, 9, 'carga postal, na modalidade contingência por meio rodoviário (LCR e LCU)', 'N', 'A');
INSERT INTO public.grupo VALUES (48, 9, 'carga postal, na modalidade nacional por meio rodoviário (LTN)', 'N', 'A');
INSERT INTO public.grupo VALUES (49, 9, 'carga postal, na modalidade regional por meio rodoviário (LTR)', 'N', 'A');
INSERT INTO public.grupo VALUES (50, 9, 'carga postal, na modalidade urbana (LTU)', 'N', 'A');
INSERT INTO public.grupo VALUES (51, 9, 'carga postal, na modalidade viagem extra por meio rodoviário', 'N', 'A');
INSERT INTO public.grupo VALUES (52, 2, 'carimbo', 'N', 'A');
INSERT INTO public.grupo VALUES (53, 6, 'carregamento e descarregamento', 'N', 'A');
INSERT INTO public.grupo VALUES (54, 7, 'monitoramento CFTV', 'N', 'A');
INSERT INTO public.grupo VALUES (55, 6, 'climatização', 'N', 'A');
INSERT INTO public.grupo VALUES (56, 6, 'coleta e entrega', 'N', 'A');
INSERT INTO public.grupo VALUES (57, 6, 'coleta, transporte e deposição de resíduos sólidos', 'N', 'A');
INSERT INTO public.grupo VALUES (58, 7, 'digitalização de documentos, com disponibilização de equipamentos e softwares', 'N', 'A');
INSERT INTO public.grupo VALUES (59, 6, 'elaboração de projetos', 'N', 'A');
INSERT INTO public.grupo VALUES (60, 5, 'engenharia - reforma', 'N', 'A');
INSERT INTO public.grupo VALUES (61, 5, 'engenharia', 'N', 'A');
INSERT INTO public.grupo VALUES (62, 4, 'entidade registrada no programa de alimentação do trabalhador - PAT', 'N', 'A');
INSERT INTO public.grupo VALUES (63, 6, 'entrega de encomendas (EIS encomenda)', 'N', 'A');
INSERT INTO public.grupo VALUES (64, 6, 'escolta', 'N', 'A');
INSERT INTO public.grupo VALUES (65, 7, 'execução indireta de serviços continuados de apoio a distribuição domiciliária (EIS postal)', 'N', 'A');
INSERT INTO public.grupo VALUES (66, 6, 'gerenciamento informatizado de abastecimento da frota', 'N', 'A');
INSERT INTO public.grupo VALUES (67, 6, 'gerenciamento informatizado de manutenção de veículos automotivos', 'N', 'A');
INSERT INTO public.grupo VALUES (68, 5, 'implantação de sistema fotovoltaico', 'N', 'A');
INSERT INTO public.grupo VALUES (69, 4, 'leiloeiros públicos oficiais', 'N', 'A');
INSERT INTO public.grupo VALUES (70, 6, 'limpeza e conservação', 'N', 'A');
INSERT INTO public.grupo VALUES (71, 7, 'limpeza predial com fornecimento de material por posto de serviço', 'N', 'A');
INSERT INTO public.grupo VALUES (72, 7, 'limpeza predial mecanizada', 'N', 'A');
INSERT INTO public.grupo VALUES (73, 6, 'limpeza predial na forma de diária de serviços e sob demanda', 'N', 'A');
INSERT INTO public.grupo VALUES (74, 7, 'limpeza predial por m2', 'N', 'A');
INSERT INTO public.grupo VALUES (75, 6, 'locação de equipamento de reprografia', 'N', 'A');
INSERT INTO public.grupo VALUES (76, 6, 'locação de equipamento operacional', 'N', 'A');
INSERT INTO public.grupo VALUES (77, 6, 'locação de veículos administrativos', 'N', 'A');
INSERT INTO public.grupo VALUES (78, 6, 'locação de veículos operacionais', 'N', 'A');
INSERT INTO public.grupo VALUES (79, 7, 'manutenção', 'N', 'A');
INSERT INTO public.grupo VALUES (80, 8, 'manutenção de ar condicionado', 'N', 'A');
INSERT INTO public.grupo VALUES (81, 8, 'manutenção de ar condicionado com ANS', 'N', 'A');
INSERT INTO public.grupo VALUES (82, 8, 'manutenção de elevador', 'N', 'A');
INSERT INTO public.grupo VALUES (83, 8, 'manutenção de elevador com ANS', 'N', 'A');
INSERT INTO public.grupo VALUES (84, 8, 'manutenção de equipamentos e sistemas', 'N', 'A');
INSERT INTO public.grupo VALUES (85, 8, 'manutenção de equipamentos e sistemas com ANS', 'N', 'A');
INSERT INTO public.grupo VALUES (86, 8, 'manutenção de subestação', 'N', 'A');
INSERT INTO public.grupo VALUES (87, 8, 'manutenção de subestação com ANS', 'N', 'A');
INSERT INTO public.grupo VALUES (88, 8, 'manutenção predial / reforma', 'N', 'A');
INSERT INTO public.grupo VALUES (89, 8, 'manutenção predial / reforma com ANS', 'N', 'A');
INSERT INTO public.grupo VALUES (90, 6, 'organização e executação de evento', 'N', 'A');
INSERT INTO public.grupo VALUES (91, 6, 'passagem automática por pedágio', 'N', 'A');
INSERT INTO public.grupo VALUES (92, 7, 'portaria', 'N', 'A');
INSERT INTO public.grupo VALUES (93, 7, 'rastreamento e monitoramento de veículos', 'N', 'I');
INSERT INTO public.grupo VALUES (94, 7, 'recepção', 'N', 'A');
INSERT INTO public.grupo VALUES (95, 6, 'seguro', 'N', 'A');
INSERT INTO public.grupo VALUES (96, 6, 'suporte técnico de TIC', 'N', 'A');
INSERT INTO public.grupo VALUES (97, 7, 'temporária (MOT)', 'N', 'A');
INSERT INTO public.grupo VALUES (98, 6, 'transporte administrativo', 'N', 'A');
INSERT INTO public.grupo VALUES (99, 6, 'transporte de bens móveis', 'N', 'A');
INSERT INTO public.grupo VALUES (100, 10, 'alienação de bens imóveis', 'N', 'A');
INSERT INTO public.grupo VALUES (101, 10, 'alienação de bens móveis', 'N', 'A');
INSERT INTO public.grupo VALUES (102, 10, 'alienação de itens de refugo', 'N', 'A');
INSERT INTO public.grupo VALUES (103, 10, 'alienação de motocicletas', 'N', 'A');
INSERT INTO public.grupo VALUES (104, 10, 'alienação de veículos', 'N', 'A');
INSERT INTO public.grupo VALUES (105, 3, 'Correios Modular (CMD) sob regime de permissão', 'N', 'A');
INSERT INTO public.grupo VALUES (106, 6, 'educação', 'N', 'A');
INSERT INTO public.grupo VALUES (107, 7, 'execução indireta de serviços continuados de apoio (EIS Tratamento)', 'N', 'A');
INSERT INTO public.grupo VALUES (108, 6, 'manutenção com fornecimento de material', 'N', 'A');
INSERT INTO public.grupo VALUES (109, 1, 'portal detector de metais, incluindo instalação', 'N', 'A');
INSERT INTO public.grupo VALUES (110, 7, 'vigilância ostensiva', 'N', 'A');
INSERT INTO public.grupo VALUES (111, 1, 'ferramentas e instrumentos', 'N', 'A');


--
-- PostgreSQL database dump complete
--

