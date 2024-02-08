------------------------------------------------------------------------------------------------
----------------------------			Сущность: Лицензия			----------------------------
------------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ekbd.license CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ekbd.license
(
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,								
	vid uuid,
    
    name character varying, 								
    series character varying,
    number character varying,
    license_type_id uuid, 
    status bool,
    --pi_id uuid, вынесено в отдельную таблицу
    purpose_id uuid,
    reason_id uuid ,
    rdate date,
    validity date,
    suser character varying,
    suser_inn character varying,
    suser_adr character varying,
    founder character varying,
    pcomp character varying,
    prev_license_id uuid,
    ssub_rf_code character varying,
    ssub_rf_id uuid,
    arctic_zone_id uuid DEFAULT NULL,
    s_license numeric(12,3),
    
    comment character varying DEFAULT NULL,
    src_hash character varying NOT NULL,
	cdate timestamp DEFAULT now(), 						
    mdate timestamp DEFAULT now(),
	geom geometry(Geometry,7683),
	
    CONSTRAINT license_pkey PRIMARY KEY (id),
	CONSTRAINT license_vid_fkey FOREIGN KEY (vid) REFERENCES ekbd.license,
	CONSTRAINT license_dic_license_type_fkey FOREIGN KEY (license_type_id) REFERENCES ekbd.dic_license_type (id),
	--CONSTRAINT license_dic_pi_fkey FOREIGN KEY (pi_id) REFERENCES ekbd.dic_pi(id),
	CONSTRAINT license_dic_purpose_fkey FOREIGN KEY (purpose_id) REFERENCES ekbd.dic_purpose(id),
	CONSTRAINT license_dic_reason_fkey FOREIGN KEY (reason_id) REFERENCES ekbd.dic_reason(id),
	CONSTRAINT license_prev_license_fkey FOREIGN KEY (prev_license_id) REFERENCES ekbd.license(id),
	CONSTRAINT license_dic_ssub_rf_fkey FOREIGN KEY (ssub_rf_id) REFERENCES ekbd.dic_ssub_rf(id),
	CONSTRAINT license_dic_arctic_zone_fkey FOREIGN KEY (arctic_zone_id) REFERENCES ekbd.dic_arctic_zone(id)
);

CREATE INDEX IF NOT EXISTS idx_license ON ekbd.license USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER license_mdate_update BEFORE UPDATE ON ekbd.license
    FOR EACH ROW
    EXECUTE PROCEDURE ekbd.f_update_mdate();

--ALTER TABLE IF EXISTS ekbd.license OWNER to ebd;
REVOKE ALL ON TABLE ekbd.license FROM ebd_integro;
GRANT ALL ON TABLE ekbd.license TO ebd;
GRANT SELECT ON TABLE ekbd.license TO ebd_integro;

COMMENT ON TABLE ekbd.license IS 'Лицензии';
COMMENT ON COLUMN ekbd.license.name IS 'Название участка';
COMMENT ON COLUMN ekbd.license.series IS 'Серия';
COMMENT ON COLUMN ekbd.license.number IS 'Номер';
COMMENT ON COLUMN ekbd.license.license_type_id IS 'Тип';
COMMENT ON COLUMN ekbd.license.status IS 'Статус';
--COMMENT ON COLUMN ekbd.license.pi_id IS 'Полезное ископаемое';
COMMENT ON COLUMN ekbd.license.purpose_id IS 'Цель';
COMMENT ON COLUMN ekbd.license.reason_id IS 'Основание выдачи';
COMMENT ON COLUMN ekbd.license.rdate IS 'Дата регистрации';
COMMENT ON COLUMN ekbd.license.validity IS 'Срок действия';
COMMENT ON COLUMN ekbd.license.suser IS 'Недропользователь';
COMMENT ON COLUMN ekbd.license.suser_inn IS 'ИНН Недропользователя';
COMMENT ON COLUMN ekbd.license.suser_adr IS 'Адрес недропользователя';
COMMENT ON COLUMN ekbd.license.founder IS 'Учредители';
COMMENT ON COLUMN ekbd.license.pcomp IS 'Головное предприятие';
COMMENT ON COLUMN ekbd.license.prev_license_id IS 'Предыдущая дицензия';
COMMENT ON COLUMN ekbd.license.ssub_rf_code IS 'Код субъекта';
COMMENT ON COLUMN ekbd.license.ssub_rf_id IS 'Субъект';
COMMENT ON COLUMN ekbd.license.arctic_zone_id IS 'Арктическая зона';
COMMENT ON COLUMN ekbd.license.s_license IS 'Площадь';
COMMENT ON COLUMN ekbd.license.comment IS 'Примечание';
COMMENT ON COLUMN ekbd.license.geom IS 'Геометрия';
COMMENT ON COLUMN ekbd.license.cdate IS 'Дата создания';
COMMENT ON COLUMN ekbd.license.mdate IS 'Дата модификации/обновления';

END;