----------------------------------------------------------------------------------------------
---------------            Сущность: Месторождения нефти и газа	          --------------------
----------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ekbd.deposit CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ekbd.deposit (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid, 

	name character varying,
    deposit_type_id uuid,
    deposit_stage_id uuid,
    dyear int,
    oblast_ssub_rf_id uuid,
    okrug_ssub_rf_id uuid,
    ngp_id uuid DEFAULT NULL,
    ngo_id uuid DEFAULT NULL, 
    ngr_id uuid DEFAULT NULL,
    arctic_zone_id uuid DEFAULT NULL,
    deposit_n_size_id uuid,
    deposit_k_size_id uuid,
    deposit_g_size_id uuid,
    deposit_k_substance_id uuid,

	comment character varying DEFAULT NULL,
	note character varying DEFAULT NULL,
	src_hash character varying NOT NULL,
	cdate timestamp DEFAULT now(), 		
    mdate timestamp DEFAULT now(),
	geom geometry(MultiPolygon,7683),

	CONSTRAINT deposit_pkey PRIMARY KEY (id),
    CONSTRAINT deposit_vid_fkey FOREIGN KEY (vid) REFERENCES ekbd.deposit,
	CONSTRAINT deposit_dic_deposit_type_fkey FOREIGN KEY (deposit_type_id) REFERENCES ekbd.dic_deposit_type(id),
	CONSTRAINT deposit_dic_deposit_stage_fkey FOREIGN KEY (deposit_stage_id) REFERENCES ekbd.dic_deposit_stage(id),
	CONSTRAINT deposit_dic_oblast_ssub_rf_type_fkey FOREIGN KEY (oblast_ssub_rf_id) REFERENCES ekbd.dic_ssub_rf(id),
	CONSTRAINT deposit_dic_okrug_ssub_rf_fkey FOREIGN KEY (okrug_ssub_rf_id) REFERENCES ekbd.dic_ssub_rf(id),
	CONSTRAINT deposit_ngp_fkey FOREIGN KEY (ngp_id) REFERENCES ekbd.ngp(id),
	CONSTRAINT deposit_ngo_fkey FOREIGN KEY (ngo_id) REFERENCES ekbd.ngo(id),
	CONSTRAINT deposit_ngr_fkey FOREIGN KEY (ngr_id) REFERENCES ekbd.ngr(id),
	CONSTRAINT deposit_dic_arctic_zone_fkey FOREIGN KEY (arctic_zone_id) REFERENCES ekbd.dic_arctic_zone(id),
	CONSTRAINT deposit_dic_deposit_n_size_fkey FOREIGN KEY (deposit_n_size_id) REFERENCES ekbd.dic_deposit_size(id),
	CONSTRAINT deposit_dic_deposit_k_size_fkey FOREIGN KEY (deposit_k_size_id) REFERENCES ekbd.dic_deposit_size(id),
	CONSTRAINT deposit_dic_deposit_g_size_fkey FOREIGN KEY (deposit_g_size_id) REFERENCES ekbd.dic_deposit_size(id),
	CONSTRAINT deposit_dic_deposit_k_substance_fkey FOREIGN KEY (deposit_k_substance_id) REFERENCES ekbd.dic_deposit_substance(id)
);

CREATE INDEX IF NOT EXISTS idx_deposit ON ekbd.deposit USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER deposit_mdate_update BEFORE UPDATE ON ekbd.deposit
    FOR EACH ROW
    EXECUTE PROCEDURE ekbd.f_update_mdate();

--ALTER TABLE IF EXISTS ekbd.deposit OWNER to ebd;
REVOKE ALL ON TABLE ekbd.deposit FROM ebd_integro;
GRANT ALL ON TABLE ekbd.deposit TO ebd;
GRANT SELECT ON TABLE ekbd.deposit TO ebd_integro;

COMMENT ON TABLE ekbd.deposit IS 'Месторождения нефти и газа';
COMMENT ON COLUMN ekbd.deposit.name IS 'Название';
COMMENT ON COLUMN ekbd.deposit.deposit_type_id IS 'Тип';
COMMENT ON COLUMN ekbd.deposit.deposit_stage_id IS 'Стадия';
COMMENT ON COLUMN ekbd.deposit.dyear IS 'Год открытия';
COMMENT ON COLUMN ekbd.deposit.oblast_ssub_rf_id IS 'Область';
COMMENT ON COLUMN ekbd.deposit.okrug_ssub_rf_id IS 'Округ';
COMMENT ON COLUMN ekbd.deposit.ngp_id IS 'НГП';
COMMENT ON COLUMN ekbd.deposit.ngo_id IS 'НГО';
COMMENT ON COLUMN ekbd.deposit.ngr_id IS 'НГР';
COMMENT ON COLUMN ekbd.deposit.arctic_zone_id IS 'Арктическая зона';
COMMENT ON COLUMN ekbd.deposit.deposit_n_size_id IS 'Извлекаемые запасы н.';
COMMENT ON COLUMN ekbd.deposit.deposit_k_size_id IS 'Извлекаемые запасы к.';
COMMENT ON COLUMN ekbd.deposit.deposit_g_size_id IS 'Геологические запасы г.';
COMMENT ON COLUMN ekbd.deposit.deposit_k_substance_id IS 'Содержание к.';
COMMENT ON COLUMN ekbd.deposit.comment IS 'Комментарий';
COMMENT ON COLUMN ekbd.deposit.note IS 'Примечание';
COMMENT ON COLUMN ekbd.deposit.geom IS 'Геометрия';
COMMENT ON COLUMN ekbd.deposit.cdate IS 'Дата создания';
COMMENT ON COLUMN ekbd.deposit.mdate IS 'Дата модификации/обновления';

END;