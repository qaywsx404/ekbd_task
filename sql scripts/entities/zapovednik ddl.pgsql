----------------------------------------------------------------------------------------------
---------------       Сущность: Особо охраняемые территории (заповедники)         ------------
----------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ekbd.zapovednik CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ekbd.zapovednik (
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
	src_hash character varying NOT NULL,
	cdate timestamp DEFAULT now(), 		
    mdate timestamp DEFAULT now(),
	geom geometry(Geometry,7683),

	CONSTRAINT zapovednik_pkey PRIMARY KEY (id),
    CONSTRAINT zapovednik_vid_fkey FOREIGN KEY (vid) REFERENCES ekbd.zapovednik,
	CONSTRAINT zapovednik_dic_zapovednik_category_fkey FOREIGN KEY (zapovednik_category_id) REFERENCES ekbd.dic_zapovednik_category(id),
	CONSTRAINT zapovednik_dic_zapovednik_importance_fkey FOREIGN KEY (zapovednik_importance_id) REFERENCES ekbd.dic_zapovednik_importance(id),
	CONSTRAINT zapovednik_dic_zapovednik_profile_fkey FOREIGN KEY (zapovednik_profile_id) REFERENCES ekbd.dic_zapovednik_profile(id),
	CONSTRAINT zapovednik_dic_zapovednik_state_fkey FOREIGN KEY (zapovednik_state_id) REFERENCES ekbd.dic_zapovednik_state(id),
	CONSTRAINT zapovednik_dic_ssub_rf_fkey FOREIGN KEY (ssub_rf_id) REFERENCES ekbd.dic_ssub_rf(id)
);

CREATE INDEX IF NOT EXISTS idx_zapovednik ON ekbd.zapovednik USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER zapovednik_mdate_update BEFORE UPDATE ON ekbd.zapovednik
    FOR EACH ROW
    EXECUTE PROCEDURE ekbd.f_update_mdate();

--ALTER TABLE IF EXISTS ekbd.zapovednik OWNER to ebd;
REVOKE ALL ON TABLE ekbd.zapovednik FROM ebd_integro;
GRANT ALL ON TABLE ekbd.zapovednik TO ebd;
GRANT SELECT ON TABLE ekbd.zapovednik TO ebd_integro;

COMMENT ON TABLE ekbd.zapovednik IS 'Особо охраняемые территории (заповедники)';
COMMENT ON COLUMN ekbd.zapovednik.name IS 'Название';
COMMENT ON COLUMN ekbd.zapovednik.zapovednik_category_id IS 'Категория';
COMMENT ON COLUMN ekbd.zapovednik.zapovednik_importance_id IS 'Значение';
COMMENT ON COLUMN ekbd.zapovednik.zapovednik_profile_id IS 'Профиль';
COMMENT ON COLUMN ekbd.zapovednik.zapovednik_state_id IS 'Текущее состояние';
COMMENT ON COLUMN ekbd.zapovednik.ssub_rf_id IS 'Регион';
COMMENT ON COLUMN ekbd.zapovednik.S_zapovednik IS 'Площадь';
COMMENT ON COLUMN ekbd.zapovednik.ohr_zona IS 'Охранная зона';
COMMENT ON COLUMN ekbd.zapovednik.rdate IS 'Дата создания';
COMMENT ON COLUMN ekbd.zapovednik.comment IS 'Комментарий';
COMMENT ON COLUMN ekbd.zapovednik.geom IS 'Геометрия';
COMMENT ON COLUMN ekbd.zapovednik.cdate IS 'Дата создания';
COMMENT ON COLUMN ekbd.zapovednik.mdate IS 'Дата модификации/обновления';

END;