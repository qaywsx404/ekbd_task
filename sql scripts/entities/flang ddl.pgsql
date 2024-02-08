------------------------------------------------------------------------------------------------
----------------------------			Сущность: Фланги			----------------------------
------------------------------------------------------------------------------------------------

--drop table ekbd.flang

BEGIN;

CREATE TABLE IF NOT EXISTS ekbd.flang
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
    CONSTRAINT flang_vid_fkey FOREIGN KEY (vid) REFERENCES ekbd.flang, 
	CONSTRAINT flang_dic_ssub_rf_fkey FOREIGN KEY (ssub_rf_id) REFERENCES ekbd.dic_ssub_rf(id),
	CONSTRAINT flang_license_fkey FOREIGN KEY (license_id) REFERENCES ekbd.license(id),
	CONSTRAINT flang_dic_flang_status_fkey FOREIGN KEY (flang_status_id) REFERENCES ekbd.dic_flang_status(id)
);

CREATE INDEX IF NOT EXISTS idx_flang ON ekbd.flang USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER flang_mdate_update BEFORE UPDATE ON ekbd.flang
    FOR EACH ROW
    EXECUTE PROCEDURE ekbd.f_update_mdate();

--ALTER TABLE IF EXISTS ekbd.flang OWNER to ebd;
REVOKE ALL ON TABLE ekbd.flang FROM ebd_integro;
GRANT ALL ON TABLE ekbd.flang TO ebd;
GRANT SELECT ON TABLE ekbd.flang TO ebd_integro;

COMMENT ON TABLE ekbd.flang IS 'Фланги';
COMMENT ON COLUMN ekbd.flang.name IS 'Название участка';
COMMENT ON COLUMN ekbd.flang.deposit IS 'Месторождение';
COMMENT ON COLUMN ekbd.flang.isFlang IS 'Является ли флангом';
COMMENT ON COLUMN ekbd.flang.s_flang IS 'Площадь';
COMMENT ON COLUMN ekbd.flang.declarant IS 'Заявитель';
COMMENT ON COLUMN ekbd.flang.edate IS 'Дата экспертизы';
COMMENT ON COLUMN ekbd.flang.resol IS 'Резолюция';
COMMENT ON COLUMN ekbd.flang.ssub_rf_id IS 'Регион';
COMMENT ON COLUMN ekbd.flang.license_id IS 'Выданная лицензия';
COMMENT ON COLUMN ekbd.flang.rdate IS 'Дата выдачи лицензии';
COMMENT ON COLUMN ekbd.flang.comment IS 'Примечание';
COMMENT ON COLUMN ekbd.flang.geom IS 'Геометрия';
COMMENT ON COLUMN ekbd.flang.cdate IS 'Дата создания';
COMMENT ON COLUMN ekbd.flang.mdate IS 'Дата модификации/обновления';

END;