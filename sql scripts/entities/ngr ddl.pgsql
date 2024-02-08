------------------------------------------------------------------------------------------------
----------------------------			Сущность: НГР			----------------------------
------------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ekbd.ngr CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ekbd.ngr (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid, 	

	name character varying,
    ngr_type_id uuid,
    ngp_id uuid DEFAULT NULL,
    ngo_id uuid DEFAULT NULL,
    index_all character varying,
    				
	comment character varying DEFAULT NULL,					
	src_hash character varying NULL,
	cdate timestamp DEFAULT now(), 				
    mdate timestamp DEFAULT now(),
	geom geometry(MultiPolygon, 987654),

	CONSTRAINT ngr_pkey PRIMARY KEY (id),
    CONSTRAINT ngr_vid_fkey FOREIGN KEY (vid) REFERENCES ekbd.ngr,  
	CONSTRAINT ngp_dic_ngr_type_fkey FOREIGN KEY (ngr_type_id) REFERENCES ekbd.dic_ngr_type(id),
    CONSTRAINT ngr_ngp_fkey FOREIGN KEY (ngp_id) REFERENCES ekbd.ngp(id),
    CONSTRAINT ngr_ngo_fkey FOREIGN KEY (ngo_id) REFERENCES ekbd.ngo(id)
);

CREATE INDEX IF NOT EXISTS idx_ngr ON ekbd.ngr USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER ngr_mdate_update BEFORE UPDATE ON ekbd.ngr
    FOR EACH ROW
    EXECUTE PROCEDURE ekbd.f_update_mdate();

ALTER TABLE IF EXISTS ekbd.ngr OWNER to ebd;
REVOKE ALL ON TABLE ekbd.ngr FROM ebd_integro;
GRANT ALL ON TABLE ekbd.ngr TO ebd;
GRANT SELECT ON TABLE ekbd.ngr TO ebd_integro;

COMMENT ON TABLE ekbd.ngr IS 'НГР';
COMMENT ON COLUMN ekbd.ngr.name IS 'Название';
COMMENT ON COLUMN ekbd.ngr.ngr_type_id IS 'Тип';
COMMENT ON COLUMN ekbd.ngr.ngp_id IS 'НГП';
COMMENT ON COLUMN ekbd.ngr.ngo_id IS 'НГО';
COMMENT ON COLUMN ekbd.ngr.index_all IS 'Индекс';
COMMENT ON COLUMN ekbd.ngr.comment IS 'Примечание';
COMMENT ON COLUMN ekbd.ngr.geom IS 'Геометрия';
COMMENT ON COLUMN ekbd.ngr.cdate IS 'Дата создания';
COMMENT ON COLUMN ekbd.ngr.mdate IS 'Дата модификации/обновления';

END;