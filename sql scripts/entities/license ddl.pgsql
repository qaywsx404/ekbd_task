------------------------------------------------------------------------------------------------
----------------------------			Сущность: Лицензия			----------------------------
------------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ebd_ekbd.license CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ebd_ekbd.license
(
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,								
	vid uuid,
    
    name character varying, 								
    series character varying,
    number character varying,
    license_type_id uuid, 
    status bool,
    pi_id uuid,
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
    S_license numeric(12,3),
    
    comment character varying DEFAULT NULL,
    geom geometry(Geometry,7683),
    src_hash character varying GENERATED ALWAYS AS (md5(
													(
													COALESCE(name, '') || COALESCE(series, '') || COALESCE(number, '')
													|| COALESCE(license_type_id::text, '') || COALESCE(pi_id::text, '') || COALESCE(purpose_id::text, '')
													|| COALESCE(reason_id::text, '') || COALESCE(ebd_ekbd.f_date_to_char(rdate), '') || COALESCE(ebd_ekbd.f_date_to_char(validity), '')
													|| COALESCE(suser, '') || COALESCE(suser_inn, '')|| COALESCE(suser_adr, '')
													|| COALESCE(founder, '') || COALESCE(pcomp, '') || COALESCE(prev_license_id::text, '') 
													|| COALESCE(ssub_rf_code, '') || COALESCE(ssub_rf_id::text, '') || COALESCE(arctic_zone_id::text, '') 
                                                    || COALESCE(S_license::text, '') || COALESCE(comment, '') || COALESCE(geom::text, '') 
													)::text
													))
                                                    STORED NOT NULL,
	cdate timestamp DEFAULT now(), 						
    mdate timestamp DEFAULT now(),
	
    CONSTRAINT license_pkey PRIMARY KEY (id),
	CONSTRAINT license_vid_fkey FOREIGN KEY (vid) REFERENCES ebd_ekbd.license,                                              -- ?
	CONSTRAINT license_dic_license_type_fkey FOREIGN KEY (license_type_id) REFERENCES ebd_ekbd.dic_license_type (id),
	CONSTRAINT license_dic_pi_fkey FOREIGN KEY (pi_id) REFERENCES ebd_ekbd.dic_pi(id),
	CONSTRAINT license_dic_purpose_fkey FOREIGN KEY (purpose_id) REFERENCES ebd_ekbd.dic_purpose(id),
	CONSTRAINT license_dic_reason_fkey FOREIGN KEY (reason_id) REFERENCES ebd_ekbd.dic_reason(id),
	CONSTRAINT license_prev_license_fkey FOREIGN KEY (prev_license_id) REFERENCES ebd_ekbd.license(id),
	CONSTRAINT license_dic_ssub_rf_fkey FOREIGN KEY (ssub_rf_id) REFERENCES ebd_ekbd.dic_ssub_rf(id), 						-- ?
	CONSTRAINT license_dic_arctic_zone_fkey FOREIGN KEY (arctic_zone_id) REFERENCES ebd_ekbd.dic_arctic_zone(id)
);



CREATE INDEX IF NOT EXISTS idx_license ON ebd_ekbd.license USING gist (geom) TABLESPACE pg_default;



ALTER TABLE IF EXISTS ebd_ekbd.license OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.license FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.license TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.license TO ebd_integro;



COMMENT ON TABLE ebd_ekbd.license IS 'Лицензии';
COMMENT ON COLUMN ebd_ekbd.license.name IS 'Название участка';
COMMENT ON COLUMN ebd_ekbd.license.series IS 'Серия';
COMMENT ON COLUMN ebd_ekbd.license.number IS 'Номер';
COMMENT ON COLUMN ebd_ekbd.license.license_type_id IS 'Тип';
COMMENT ON COLUMN ebd_ekbd.license.status IS 'Статус';
COMMENT ON COLUMN ebd_ekbd.license.pi_id IS 'Полезное ископаемое';
COMMENT ON COLUMN ebd_ekbd.license.purpose_id IS 'Цель';
COMMENT ON COLUMN ebd_ekbd.license.reason_id IS 'Основание выдачи';
COMMENT ON COLUMN ebd_ekbd.license.rdate IS 'Дата регистрации';
COMMENT ON COLUMN ebd_ekbd.license.validity IS 'Срок действия';
COMMENT ON COLUMN ebd_ekbd.license.suser IS 'Недропользователь';
COMMENT ON COLUMN ebd_ekbd.license.suser_inn IS 'ИНН Недропользователя';
COMMENT ON COLUMN ebd_ekbd.license.suser_adr IS 'Адрес недропользователя';
COMMENT ON COLUMN ebd_ekbd.license.founder IS 'Учредители';
COMMENT ON COLUMN ebd_ekbd.license.pcomp IS 'Головное предприятие';
COMMENT ON COLUMN ebd_ekbd.license.prev_license_id IS 'Предыдущая дицензия';
COMMENT ON COLUMN ebd_ekbd.license.ssub_rf_code IS 'Код субъекта';
COMMENT ON COLUMN ebd_ekbd.license.ssub_rf_id IS 'Субъект';
COMMENT ON COLUMN ebd_ekbd.license.arctic_zone_id IS 'Арктическая зона';
COMMENT ON COLUMN ebd_ekbd.license.S_license IS 'Площадь';
COMMENT ON COLUMN ebd_ekbd.license.comment IS 'Примечание';
COMMENT ON COLUMN ebd_ekbd.license.geom IS 'Геометрия';
COMMENT ON COLUMN ebd_ekbd.license.cdate IS 'Дата создания';
COMMENT ON COLUMN ebd_ekbd.license.mdate IS 'Дата модификации/обновления';

END;