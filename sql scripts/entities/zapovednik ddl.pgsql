----------------------------------------------------------------------------------------------
---------------       Сущность: Особо охраняемые территории (заповедники)         ------------
----------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ebd_ekbd.zapovednik CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ebd_ekbd.zapovednik (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid,

	name character varying,
    zapovednik_category_id uuid,
    zapovednik_importance_id uuid,
    zapovednik_profile_id uuid,
    zapovednik_state_id uuid,
    ssub_rf_id uuid,
    S_zapovednik numeric(12,3),
    ohr_zona numeric(12,3),
    rdate date,

	comment character varying DEFAULT NULL,
	src_hash character varying GENERATED ALWAYS AS (md5(
                                                    (
                                                    COALESCE(name, '') || COALESCE(zapovednik_category_id::text, '') || COALESCE(zapovednik_importance_id::text, '')
                                                    || COALESCE(zapovednik_profile_id::text, '') || COALESCE(zapovednik_state_id::text, '') || COALESCE(ssub_rf_id::text, '')
                                                    || COALESCE(S_zapovednik::text, '') || COALESCE(ohr_zona::text, '') || COALESCE(ebd_ekbd.f_date_to_char(rdate), '')
                                                    || COALESCE(comment, '') || COALESCE(geom::text, '')
                                                    )
									                ))
                                                     STORED NOT NULL,
	cdate timestamp DEFAULT now(), 		
    mdate timestamp DEFAULT now(),
	geom geometry(MultiPolygon,7683),

	CONSTRAINT zapovednik_pkey PRIMARY KEY (id),
    CONSTRAINT zapovednik_vid_fkey FOREIGN KEY (vid) REFERENCES ebd_ekbd.zapovednik,
	CONSTRAINT zapovednik_dic_zapovednik_category_fkey FOREIGN KEY (zapovednik_category_id) REFERENCES ebd_ekbd.dic_zapovednik_category(id),
	CONSTRAINT zapovednik_dic_zapovednik_importance_fkey FOREIGN KEY (zapovednik_importance_id) REFERENCES ebd_ekbd.dic_zapovednik_importance(id),
	CONSTRAINT zapovednik_dic_zapovednik_profile_fkey FOREIGN KEY (zapovednik_profile_id) REFERENCES ebd_ekbd.dic_zapovednik_profile(id),
	CONSTRAINT zapovednik_dic_zapovednik_state_fkey FOREIGN KEY (zapovednik_state_id) REFERENCES ebd_ekbd.dic_zapovednik_state(id),
	CONSTRAINT zapovednik_dic_ssub_rf_fkey FOREIGN KEY (ssub_rf_id) REFERENCES ebd_ekbd.dic_ssub_rf(id)
);

CREATE INDEX IF NOT EXISTS idx_zapovednik ON ebd_ekbd.zapovednik USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER zapovednik_mdate_update BEFORE UPDATE ON ebd_ekbd.zapovednik
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

ALTER TABLE IF EXISTS ebd_ekbd.zapovednik OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.zapovednik FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.zapovednik TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.zapovednik TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.zapovednik IS 'Особо охраняемые территории (заповедники)';
COMMENT ON COLUMN ebd_ekbd.zapovednik.name IS 'Название';
COMMENT ON COLUMN ebd_ekbd.zapovednik.zapovednik_category_id IS 'Категория';
COMMENT ON COLUMN ebd_ekbd.zapovednik.zapovednik_importance_id IS 'Значение';
COMMENT ON COLUMN ebd_ekbd.zapovednik.zapovednik_profile_id IS 'Профиль';
COMMENT ON COLUMN ebd_ekbd.zapovednik.zapovednik_state_id IS 'Текущее состояние';
COMMENT ON COLUMN ebd_ekbd.zapovednik.ssub_rf_id IS 'Регион';
COMMENT ON COLUMN ebd_ekbd.zapovednik.S_zapovednik IS 'Площадь';
COMMENT ON COLUMN ebd_ekbd.zapovednik.ohr_zona IS 'Охранная зона';
COMMENT ON COLUMN ebd_ekbd.zapovednik.rdate IS 'Дата создания';
COMMENT ON COLUMN ebd_ekbd.zapovednik.comment IS 'Комментарий';
COMMENT ON COLUMN ebd_ekbd.zapovednik.geom IS 'Геометрия';
COMMENT ON COLUMN ebd_ekbd.zapovednik.cdate IS 'Дата создания';
COMMENT ON COLUMN ebd_ekbd.zapovednik.mdate IS 'Дата модификации/обновления';

END;