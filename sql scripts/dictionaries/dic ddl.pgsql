----------------------------------------------------------------------------------------
---------------------------------- Словари ---------------------------------------------
----------------------------------------------------------------------------------------
-- Словари:
-- 1.   Тип лицензии (license_type)
-- 2.   Полезное ископаемое (pi)
-- 3.   Цель (purpose)
-- 4.   Основание выдачи лицензии (reason)
-- 5.   Статус фланга (flang_status)
-- 6.   Форма состязаний (comp_form)
-- 7.   Арктическая зона (arctic_zone)
-- 8.   Стадия разработки месторождения (deposit_stage)
-- 9.   Размер месторождения (deposit_size)
-- 10.  Тип месторождения (deposit_type)
-- 11.  Размер месторождения по содержанию конденсата (deposit_substance)
-- 12.  Категория заповедника (zapovednik_category)
-- 13.  Значение заповедника (zapovednik_importance)
-- 14.  Профиль заповедника (zapovednik_profile)
-- 15.  Текущее состояние заповедника (zapovednik_state)
-- 16.  Тип НГП (ngp_type)
-- 17.  Тип НГО (ngo_type)
-- 18.  Тип НГР (ngr_type)
--
-- 19. Справочник административно-территориальных образований



BEGIN;

---------------------
-- 1. Тип лицензии --
---------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_license_type (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_license_type_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_license_type OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_license_type FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_license_type TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_license_type TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_license_type IS 'Тип лицензии';
COMMENT ON COLUMN ebd_ekbd.dic_license_type.cdate IS 'Дата создания';



----------------------------
-- 2. Полезное ископаемое --
----------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_pi (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_pi_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_pi OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_pi FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_pi TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_pi TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_pi IS 'Полезное ископаемое';
COMMENT ON COLUMN ebd_ekbd.dic_pi.cdate IS 'Дата создания';



-------------
-- 3. Цель --
-------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_purpose (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_purpose_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_purpose OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_purpose FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_purpose TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_purpose TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_purpose IS 'Цель';
COMMENT ON COLUMN ebd_ekbd.dic_purpose.cdate IS 'Дата создания';



----------------------------------
-- 4. Основание выдачи лицензии --
----------------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_reason (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_reason_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_reason OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_reason FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_reason TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_reason TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_reason IS 'Основание выдачи лицензии';
COMMENT ON COLUMN ebd_ekbd.dic_reason.cdate IS 'Дата создания';



----------------------
-- 5. Статус фланга --
----------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_flang_status (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_flang_status_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_flang_status OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_flang_status FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_flang_status TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_flang_status TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_flang_status IS 'Статус фланга';
COMMENT ON COLUMN ebd_ekbd.dic_flang_status.cdate IS 'Дата создания';



-------------------------
-- 6. Форма состязаний --
-------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_comp_form (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_comp_form_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_comp_form OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_comp_form FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_comp_form TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_comp_form TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_comp_form IS 'Форма состязаний';
COMMENT ON COLUMN ebd_ekbd.dic_comp_form.cdate IS 'Дата создания';



-------------------------
-- 7. Арктическая зона --
-------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_arctic_zone (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_arctic_zone_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_arctic_zone OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_arctic_zone FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_arctic_zone TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_arctic_zone TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_arctic_zone IS 'Арктическая зона';
COMMENT ON COLUMN ebd_ekbd.dic_arctic_zone.cdate IS 'Дата создания';



----------------------------------------
-- 8. Стадия разработки месторождения --
----------------------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_deposit_stage (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_deposit_stage_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_deposit_stage OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_deposit_stage FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_deposit_stage TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_deposit_stage TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_deposit_stage IS 'Стадия разработки месторождения';
COMMENT ON COLUMN ebd_ekbd.dic_deposit_stage.cdate IS 'Дата создания';



-----------------------------
-- 9. Размер месторождения --
-----------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_deposit_size (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_deposit_size_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_deposit_size OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_deposit_size FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_deposit_size TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_deposit_size TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_deposit_size IS 'Размер месторождения';
COMMENT ON COLUMN ebd_ekbd.dic_deposit_size.cdate IS 'Дата создания';



---------------------------
-- 10. Тип месторождения --
---------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_deposit_type (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_deposit_type_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_deposit_type OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_deposit_type FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_deposit_type TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_deposit_type TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_deposit_type IS 'Тип месторождения';
COMMENT ON COLUMN ebd_ekbd.dic_deposit_type.cdate IS 'Дата создания';



-------------------------------------------------------
-- 11. Размер месторождения по содержанию конденсата --
-------------------------------------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_deposit_substance (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_deposit_substance_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_deposit_substance OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_deposit_substance FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_deposit_substance TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_deposit_substance TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_deposit_substance IS 'Размер месторождения по содержанию конденсата';
COMMENT ON COLUMN ebd_ekbd.dic_deposit_substance.cdate IS 'Дата создания';



-------------------------------
-- 12. Категория заповедника --
-------------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_zapovednik_category (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_zapovednik_category_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_zapovednik_category OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_zapovednik_category FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_zapovednik_category TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_zapovednik_category TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_zapovednik_category IS 'Категория заповедника';
COMMENT ON COLUMN ebd_ekbd.dic_zapovednik_category.cdate IS 'Дата создания';



------------------------------
-- 13. Значение заповедника --
------------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_zapovednik_importance (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_zapovednik_importance_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_zapovednik_importance OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_zapovednik_importance FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_zapovednik_importance TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_zapovednik_importance TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_zapovednik_importance IS 'Значение заповедника';
COMMENT ON COLUMN ebd_ekbd.dic_zapovednik_importance.cdate IS 'Дата создания';



-----------------------------
-- 14. Профиль заповедника --
-----------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_zapovednik_profile (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_zapovednik_profile_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_zapovednik_profile OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_zapovednik_profile FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_zapovednik_profile TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_zapovednik_profile TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_zapovednik_profile IS 'Профиль заповедника';
COMMENT ON COLUMN ebd_ekbd.dic_zapovednik_profile.cdate IS 'Дата создания';


---------------------------------------
-- 15. Текущее состояние заповедника --
---------------------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_zapovednik_state (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_zapovednik_state_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_zapovednik_state OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_zapovednik_state FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_zapovednik_state TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_zapovednik_state TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_zapovednik_state IS 'Текущее состояние заповедника';
COMMENT ON COLUMN ebd_ekbd.dic_zapovednik_state.cdate IS 'Дата создания';



-----------------
-- 16. Тип НГП --
-----------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_ngp_type (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_ngp_type_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_ngp_type OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_ngp_type FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_ngp_type TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_ngp_type TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_ngp_type IS 'Тип НГП';
COMMENT ON COLUMN ebd_ekbd.dic_ngp_type.cdate IS 'Дата создания';



-----------------
-- 17. Тип НГО --
-----------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_ngo_type (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_ngo_type_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_ngo_type OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_ngo_type FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_ngo_type TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_ngo_type TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_ngo_type IS 'Тип НГО';
COMMENT ON COLUMN ebd_ekbd.dic_ngo_type.cdate IS 'Дата создания';



-----------------
-- 18. Тип НГР --
-----------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_ngr_type (
	id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,
	CONSTRAINT dic_ngr_type_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_ngr_type OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_ngr_type FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_ngr_type TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_ngr_type TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_ngr_type IS 'Тип НГР';
COMMENT ON COLUMN ebd_ekbd.dic_ngr_type.cdate IS 'Дата создания';



----------------------------------------------------------------
-- 19. Справочник административно-территориальных образований --
----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ebd_ekbd.dic_ssub_rf (
    id uuid DEFAULT uuid_generate_v4(),
    cdate timestamp DEFAULT now(),
    value character varying NOT NULL,

    bal_result_name character varying COLLATE pg_catalog."default",
    hist_name character varying COLLATE pg_catalog."default",
    okato_str character varying COLLATE pg_catalog."default",
    level_id integer,
    bal_sort integer,
    CONSTRAINT dic_ngr_type_pkey PRIMARY KEY (id)
    -- CONSTRAINT dic_ssub_rf_hierarchy FOREIGN KEY (pid)
    --     REFERENCES ebd_ekbd.dic_ssub_rf (id) MATCH SIMPLE
    --     ON UPDATE NO ACTION
    --     ON DELETE NO ACTION
);

ALTER TABLE IF EXISTS ebd_ekbd.dic_ssub_rf OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.dic_ssub_rf FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.dic_ssub_rf TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.dic_ssub_rf TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.dic_ssub_rf IS 'Справочник административно-территориальных образований';
COMMENT ON COLUMN ebd_ekbd.dic_ssub_rf.cdate IS 'Дата создания';





END;