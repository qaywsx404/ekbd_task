------------------------------------------------------------------------------------------------
----------------------------			Сущность: НГР			----------------------------
------------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ebd_ekbd.ngr CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ebd_ekbd.ngr (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid, 	

	name character varying,
    ngr_type_id uuid,
    ngp_id	uuid DEFAULT NULL,
    ngo_id	uuid DEFAULT NULL,
    index_all character varying,
    				
	comment character varying DEFAULT NULL,					
	src_hash character varying NULL,
	cdate timestamp DEFAULT now(), 				
    mdate timestamp DEFAULT now(),
	geom geometry(MultiPolygon, 987654),

	CONSTRAINT ngr_pkey PRIMARY KEY (id),
    CONSTRAINT ngr_vid_fkey FOREIGN KEY (vid) REFERENCES ebd_ekbd.ngr,  
	CONSTRAINT ngp_dic_ngr_type_fkey FOREIGN KEY (ngr_type_id) REFERENCES ebd_ekbd.dic_ngr_type(id),
    CONSTRAINT ngr_ngp_fkey FOREIGN KEY (ngp_id) REFERENCES ebd_ekbd.ngp(id),
    CONSTRAINT ngr_ngo_fkey FOREIGN KEY (ngo_id) REFERENCES ebd_ekbd.ngo(id)
);

CREATE INDEX IF NOT EXISTS idx_ngr ON ebd_ekbd.ngr USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER ngr_mdate_update BEFORE UPDATE ON ebd_ekbd.ngr
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

ALTER TABLE IF EXISTS ebd_ekbd.ngr OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.ngr FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.ngr TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.ngr TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.ngr IS 'НГР';
COMMENT ON COLUMN ebd_ekbd.ngr.name IS 'Название';
COMMENT ON COLUMN ebd_ekbd.ngr.ngr_type_id IS 'Тип';
COMMENT ON COLUMN ebd_ekbd.ngr.ngp_id IS 'НГП';
COMMENT ON COLUMN ebd_ekbd.ngr.ngo_id IS 'НГО';
COMMENT ON COLUMN ebd_ekbd.ngr.index_all IS 'Индекс';
COMMENT ON COLUMN ebd_ekbd.ngr.comment IS 'Примечание';
COMMENT ON COLUMN ebd_ekbd.ngr.geom IS 'Геометрия';
COMMENT ON COLUMN ebd_ekbd.ngr.cdate IS 'Дата создания';
COMMENT ON COLUMN ebd_ekbd.ngr.mdate IS 'Дата модификации/обновления';

END;