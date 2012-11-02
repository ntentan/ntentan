--
-- TOC entry 162 (class 1259 OID 330209)
-- Dependencies: 6
-- Name: departments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE departments (
    id integer NOT NULL,
    name character varying(255) NOT NULL
);


--
-- TOC entry 161 (class 1259 OID 330207)
-- Dependencies: 6 162
-- Name: departments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE departments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1921 (class 0 OID 0)
-- Dependencies: 161
-- Name: departments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE departments_id_seq OWNED BY departments.id;


--
-- TOC entry 164 (class 1259 OID 330215)
-- Dependencies: 6
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE roles (
    id integer NOT NULL,
    name character varying(255) NOT NULL
);


--
-- TOC entry 163 (class 1259 OID 330213)
-- Dependencies: 6 164
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1922 (class 0 OID 0)
-- Dependencies: 163
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE roles_id_seq OWNED BY roles.id;


--
-- TOC entry 166 (class 1259 OID 330221)
-- Dependencies: 1904 1905 6
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE users (
    id integer NOT NULL,
    username character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    role_id integer NOT NULL,
    firstname character varying(255) NOT NULL,
    lastname character varying(255) NOT NULL,
    othernames character varying(255) DEFAULT NULL::character varying,
    status integer NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(64) DEFAULT NULL::character varying,
    office integer,
    last_login_time timestamp without time zone,
    is_admin boolean
);


--
-- TOC entry 165 (class 1259 OID 330219)
-- Dependencies: 166 6
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1923 (class 0 OID 0)
-- Dependencies: 165
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- TOC entry 1901 (class 2604 OID 330212)
-- Dependencies: 161 162 162
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY departments ALTER COLUMN id SET DEFAULT nextval('departments_id_seq'::regclass);


--
-- TOC entry 1902 (class 2604 OID 330218)
-- Dependencies: 164 163 164
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY roles ALTER COLUMN id SET DEFAULT nextval('roles_id_seq'::regclass);


--
-- TOC entry 1903 (class 2604 OID 330224)
-- Dependencies: 166 165 166
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- TOC entry 1907 (class 2606 OID 330235)
-- Dependencies: 162 162 1915
-- Name: departments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY departments
    ADD CONSTRAINT departments_pkey PRIMARY KEY (id);


--
-- TOC entry 1909 (class 2606 OID 330233)
-- Dependencies: 164 164 1915
-- Name: roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 1911 (class 2606 OID 330237)
-- Dependencies: 166 166 1915
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 1913 (class 2606 OID 330243)
-- Dependencies: 162 166 1906 1915
-- Name: users_office_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_office_fkey FOREIGN KEY (office) REFERENCES departments(id);


--
-- TOC entry 1912 (class 2606 OID 330238)
-- Dependencies: 166 1908 164 1915
-- Name: users_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_role_id_fkey FOREIGN KEY (role_id) REFERENCES roles(id);


-- Completed on 2012-11-02 09:37:09 GMT

--
-- PostgreSQL database dump complete
--

