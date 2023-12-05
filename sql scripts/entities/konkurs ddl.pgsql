------------------------------------------------------------------------------------------------
---------------       Сущность: Участки, предлагаемые к лицензированию	    --------------------
------------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ebd_ekbd.konkurs CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ebd_ekbd.konkurs
(
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid,
    	
	name character varying,
    license_type_id uuid,
    konkurs_pi_id uuid,
    purpose_id uuid,
    ryear character varying,
    comp_form_id uuid,
    ssub_rf_id uuid,
    s_konkurs numeric(12,3),
    prev_konkurs_id uuid DEFAULT NULL,
    prev_txt character varying DEFAULT NULL,
    arctic_zone_id uuid DEFAULT NULL,
    reserves_n character varying DEFAULT NULL,
    reserves_g character varying DEFAULT NULL,				
    reserves_k character varying DEFAULT NULL,
    resource_n character varying DEFAULT NULL,
    resource_g character varying DEFAULT NULL,
    resource_k character varying DEFAULT NULL,
	
    comment character varying DEFAULT NULL,
	src_hash character varying NOT NULL,
	cdate timestamp DEFAULT now(),
    mdate timestamp DEFAULT now(),
	geom geometry(MultiPolygon,7683),

	CONSTRAINT konkurs_pkey PRIMARY KEY (id),
    CONSTRAINT konkurs_vid_fkey FOREIGN KEY (vid) REFERENCES ebd_ekbd.konkurs,               
	CONSTRAINT konkurs_license_type_fkey FOREIGN KEY (license_type_id) REFERENCES ebd_ekbd.dic_license_type(id),
	CONSTRAINT konkurs_rel_konkurs_pi_fkey FOREIGN KEY (konkurs_pi_id) REFERENCES ebd_ekbd.rel_konkurs_pi(id),
	CONSTRAINT konkurs_dic_purpose_fkey FOREIGN KEY (purpose_id) REFERENCES ebd_ekbd.dic_purpose(id),
	CONSTRAINT konkurs_dic_comp_form_fkey FOREIGN KEY (comp_form_id) REFERENCES ebd_ekbd.dic_comp_form(id),
	CONSTRAINT konkurs_dic_ssub_rf_fkey FOREIGN KEY (ssub_rf_id) REFERENCES ebd_ekbd.dic_ssub_rf(id),
	CONSTRAINT konkurs_prev_konkurs_fkey FOREIGN KEY (prev_konkurs_id) REFERENCES ebd_ekbd.konkurs(id)
);

CREATE INDEX IF NOT EXISTS idx_konkurs ON ebd_ekbd.konkurs USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER konkurs_mdate_update BEFORE UPDATE ON ebd_ekbd.konkurs
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

ALTER TABLE IF EXISTS ebd_ekbd.konkurs OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.konkurs FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.konkurs TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.konkurs TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.konkurs IS 'Участки, предлагаемые к лицензированию';
COMMENT ON COLUMN ebd_ekbd.konkurs.name IS 'Название';
COMMENT ON COLUMN ebd_ekbd.konkurs.license_type_id IS 'Тип лицензии';
COMMENT ON COLUMN ebd_ekbd.konkurs.konkurs_pi_id IS 'Полезное ископаемое';
COMMENT ON COLUMN ebd_ekbd.konkurs.purpose_id IS 'Цель';
COMMENT ON COLUMN ebd_ekbd.konkurs.ryear IS 'Год включения';
COMMENT ON COLUMN ebd_ekbd.konkurs.comp_form_id IS 'Форма состязаний';
COMMENT ON COLUMN ebd_ekbd.konkurs.ssub_rf_id IS 'Регион';
COMMENT ON COLUMN ebd_ekbd.konkurs.S_konkurs IS 'Площадь';
COMMENT ON COLUMN ebd_ekbd.konkurs.prev_konkurs_id IS 'Переходящий id';
COMMENT ON COLUMN ebd_ekbd.konkurs.prev_txt IS 'Переходящий текст';
COMMENT ON COLUMN ebd_ekbd.konkurs.arctic_zone_id IS 'Арктическая зона';
COMMENT ON COLUMN ebd_ekbd.konkurs.reserves_n IS 'Запасы н.';
COMMENT ON COLUMN ebd_ekbd.konkurs.reserves_g IS 'Запасы газа';
COMMENT ON COLUMN ebd_ekbd.konkurs.reserves_k IS 'Запасы к.';
COMMENT ON COLUMN ebd_ekbd.konkurs.resource_n IS 'Ресурсы н.';
COMMENT ON COLUMN ebd_ekbd.konkurs.resource_g IS 'Ресурсы газа';
COMMENT ON COLUMN ebd_ekbd.konkurs.resource_k IS 'Ресурсы к.';
COMMENT ON COLUMN ebd_ekbd.konkurs.comment IS 'Примечание';
COMMENT ON COLUMN ebd_ekbd.konkurs.geom IS 'Геометрия';
COMMENT ON COLUMN ebd_ekbd.konkurs.cdate IS 'Дата создания';
COMMENT ON COLUMN ebd_ekbd.konkurs.mdate IS 'Дата модификации/обновления';

END;