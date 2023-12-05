------------------------------------------------------------------------------------------------
----------------------------			Сущность: Фланги			----------------------------
------------------------------------------------------------------------------------------------

--drop table ebd_ekbd.flang

BEGIN;

CREATE TABLE IF NOT EXISTS ebd_ekbd.flang
(
    id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid, 	

    name character varying,
	deposit character varying,
	isFlang character varying,
	s_flang numeric(12, 3),
	declarant character varying,
	edate character varying,
	resol character varying,
	ssub_rf_id uuid,
	license_id uuid,
	rdate date,
	flang_status_id uuid,

	comment character varying DEFAULT NULL,
	src_hash character varying NOT NULL,
	cdate timestamp DEFAULT now(),
    mdate timestamp DEFAULT now(),
	geom geometry(MultiPolygon,7683),
	
    CONSTRAINT flang_pkey PRIMARY KEY (id),
    CONSTRAINT flang_vid_fkey FOREIGN KEY (vid) REFERENCES ebd_ekbd.flang, 
	CONSTRAINT flang_dic_ssub_rf_fkey FOREIGN KEY (ssub_rf_id) REFERENCES ebd_ekbd.dic_ssub_rf(id),
	CONSTRAINT flang_license_fkey FOREIGN KEY (license_id) REFERENCES ebd_ekbd.license(id),
	CONSTRAINT flang_dic_flang_status_fkey FOREIGN KEY (flang_status_id) REFERENCES ebd_ekbd.dic_flang_status(id)
);

CREATE INDEX IF NOT EXISTS idx_flang ON ebd_ekbd.flang USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER flang_mdate_update BEFORE UPDATE ON ebd_ekbd.flang
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

ALTER TABLE IF EXISTS ebd_ekbd.flang OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.flang FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.flang TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.flang TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.flang IS 'Фланги';
COMMENT ON COLUMN ebd_ekbd.flang.name IS 'Название участка';
COMMENT ON COLUMN ebd_ekbd.flang.deposit IS 'Месторождение';
COMMENT ON COLUMN ebd_ekbd.flang.isFlang IS 'Является ли флангом';
COMMENT ON COLUMN ebd_ekbd.flang.s_flang IS 'Площадь';
COMMENT ON COLUMN ebd_ekbd.flang.declarant IS 'Заявитель';
COMMENT ON COLUMN ebd_ekbd.flang.edate IS 'Дата экспертизы';
COMMENT ON COLUMN ebd_ekbd.flang.resol IS 'Резолюция';
COMMENT ON COLUMN ebd_ekbd.flang.ssub_rf_id IS 'Регион';
COMMENT ON COLUMN ebd_ekbd.flang.license_id IS 'Выданная лицензия';
COMMENT ON COLUMN ebd_ekbd.flang.rdate IS 'Дата выдачи лицензии';
COMMENT ON COLUMN ebd_ekbd.flang.comment IS 'Примечание';
COMMENT ON COLUMN ebd_ekbd.flang.geom IS 'Геометрия';
COMMENT ON COLUMN ebd_ekbd.flang.cdate IS 'Дата создания';
COMMENT ON COLUMN ebd_ekbd.flang.mdate IS 'Дата модификации/обновления';

END;