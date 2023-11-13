----------------------------------------------------------------------------------------------
---------------            Сущность: Нефтегазоперспективные структуры	          ------------
----------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ebd_ekbd.struct CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ebd_ekbd.struct (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid, 

	name character varying,
    deposit_type_id uuid,
    deposit_stage_id uuid,
    ng_struct character varying,
    oblast_ssub_rf_id uuid,
    okrug_ssub_rf_id uuid,
    ngp_id uuid DEFAULT NULL,
    ngo_id uuid DEFAULT NULL,
    ngr_id uuid DEFAULT NULL,
    arctic_zone_id uuid DEFAULT NULL,
    syear int,
    lastyear int,
    nf character varying,
    gr_n numeric(12,3),
    gr_g numeric(12,3),
    gr_k numeric(12,3),
    ir_n numeric(12,3),
    ir_k numeric(12,3),
    rdl_n numeric(12,3),
    rdl_g numeric(12,3),
    rdl_k numeric(12,3),

	comment character varying DEFAULT NULL,
    geom geometry(MultiPolygon,7683),
	src_hash character varying GENERATED ALWAYS AS (md5(
                                                    (
                                                    COALESCE(name, '') || COALESCE(deposit_type_id::text, '') || COALESCE(deposit_stage_id::text, '')
                                                    || COALESCE(ng_struct, '') || COALESCE(oblast_ssub_rf_id::text, '') || COALESCE(okrug_ssub_rf_id::text, '')
                                                    || COALESCE(ngp_id::text, '') || COALESCE(ngo_id::text, '') || COALESCE(ngr_id::text, '')
                                                    || COALESCE(arctic_zone_id::text, '') || COALESCE(syear::text, '') || COALESCE(lastyear::text, '')
                                                    || COALESCE(nf, '') || COALESCE(gr_n::text, '') || COALESCE(gr_g::text, '')
                                                    || COALESCE(gr_k::text, '') || COALESCE(ir_n::text, '') || COALESCE(ir_k::text, '')
                                                    || COALESCE(rdl_n::text, '') || COALESCE(rdl_g::text, '') || COALESCE(rdl_k::text, '')
                                                    || COALESCE(comment, '') || COALESCE(geom::text, '')
                                                    )
									                 ))
                                                     STORED NOT NULL,
	cdate timestamp DEFAULT now(), 		
    mdate timestamp DEFAULT now(),

	CONSTRAINT struct_pkey PRIMARY KEY (id),
    CONSTRAINT struct_vid_fkey FOREIGN KEY (vid) REFERENCES ebd_ekbd.struct,
	CONSTRAINT struct_dic_deposit_type_fkey FOREIGN KEY (deposit_type_id) REFERENCES ebd_ekbd.dic_deposit_type(id),
	CONSTRAINT struct_dic_deposit_stage_fkey FOREIGN KEY (deposit_stage_id) REFERENCES ebd_ekbd.dic_deposit_stage(id),
	CONSTRAINT struct_dic_oblast_ssub_rf_fkey FOREIGN KEY (oblast_ssub_rf_id) REFERENCES ebd_ekbd.dic_ssub_rf(id),
	CONSTRAINT struct_dic_okrug_ssub_rf_fkey FOREIGN KEY (okrug_ssub_rf_id) REFERENCES ebd_ekbd.dic_ssub_rf(id),
	CONSTRAINT struct_ngp_fkey FOREIGN KEY (ngp_id) REFERENCES ebd_ekbd.ngp(id),
	CONSTRAINT struct_ngo_fkey FOREIGN KEY (ngo_id) REFERENCES ebd_ekbd.ngo(id),
	CONSTRAINT struct_ngr_fkey FOREIGN KEY (ngr_id) REFERENCES ebd_ekbd.ngr(id),
	CONSTRAINT struct_dic_arctic_zone_fkey FOREIGN KEY (arctic_zone_id) REFERENCES ebd_ekbd.dic_arctic_zone(id)
);



CREATE INDEX IF NOT EXISTS idx_struct ON ebd_ekbd.struct USING gist (geom) TABLESPACE pg_default;



ALTER TABLE IF EXISTS ebd_ekbd.struct OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.struct FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.struct TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.struct TO ebd_integro;



COMMENT ON TABLE ebd_ekbd.struct IS 'Нефтегазоперспективные структуры';
COMMENT ON COLUMN ebd_ekbd.struct.name IS 'Название';
COMMENT ON COLUMN ebd_ekbd.struct.deposit_type_id IS 'Тип';
COMMENT ON COLUMN ebd_ekbd.struct.deposit_stage_id IS 'Стадия';
COMMENT ON COLUMN ebd_ekbd.struct.ng_struct IS 'Отложения';
COMMENT ON COLUMN ebd_ekbd.struct.oblast_ssub_rf_id IS 'Область';
COMMENT ON COLUMN ebd_ekbd.struct.okrug_ssub_rf_id IS 'Округ';
COMMENT ON COLUMN ebd_ekbd.struct.ngp_id IS 'НГП';
COMMENT ON COLUMN ebd_ekbd.struct.ngo_id IS 'НГО';
COMMENT ON COLUMN ebd_ekbd.struct.ngr_id IS 'НГР';
COMMENT ON COLUMN ebd_ekbd.struct.arctic_zone_id IS 'Арктическая зона';
COMMENT ON COLUMN ebd_ekbd.struct.syear IS 'Год ввода';
COMMENT ON COLUMN ebd_ekbd.struct.lastyear IS 'Год списания';
COMMENT ON COLUMN ebd_ekbd.struct.nf IS 'НФ';
COMMENT ON COLUMN ebd_ekbd.struct.gr_n IS 'Геологические ресурсы н.';
COMMENT ON COLUMN ebd_ekbd.struct.gr_g IS 'Геологические ресурсы г.';
COMMENT ON COLUMN ebd_ekbd.struct.gr_k IS 'Геологические ресурсы к.';
COMMENT ON COLUMN ebd_ekbd.struct.ir_n IS 'Извлекаемые ресурсы н.';
COMMENT ON COLUMN ebd_ekbd.struct.ir_k IS 'Извлекаемые ресурсы к.';
COMMENT ON COLUMN ebd_ekbd.struct.rdl_n IS 'Ресурсы дл н.';
COMMENT ON COLUMN ebd_ekbd.struct.rdl_g IS 'Ресурсы дл г.';
COMMENT ON COLUMN ebd_ekbd.struct.rdl_k IS 'Ресурсы дл к.';
COMMENT ON COLUMN ebd_ekbd.struct.comment IS 'Комментарий';
COMMENT ON COLUMN ebd_ekbd.struct.geom IS 'Геометрия';
COMMENT ON COLUMN ebd_ekbd.struct.cdate IS 'Дата создания';
COMMENT ON COLUMN ebd_ekbd.struct.mdate IS 'Дата модификации/обновления';

END;