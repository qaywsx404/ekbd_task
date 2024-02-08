------------------------------------------------------------------------------------------------
----------------------------			Сущность: НГО			----------------------------
------------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ekbd.ngo CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ekbd.ngo (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid, 	

	name character varying,
    ngo_type_id uuid,
    ngp_id	uuid DEFAULT NULL,
    index_all character varying,
    				
	comment character varying DEFAULT NULL,
	src_hash character varying NULL,
	cdate timestamp DEFAULT now(), 				
    mdate timestamp DEFAULT now(),
	geom geometry(MultiPolygon, 987654),

	CONSTRAINT ngo_pkey PRIMARY KEY (id),
    CONSTRAINT ngo_vid_fkey FOREIGN KEY (vid) REFERENCES ekbd.ngo,  
	CONSTRAINT ngo_dic_ngo_type_fkey FOREIGN KEY (ngo_type_id) REFERENCES ekbd.dic_ngo_type(id),
	CONSTRAINT ngo_ngp_fkey FOREIGN KEY (ngp_id) REFERENCES ekbd.ngp(id)
);

CREATE INDEX IF NOT EXISTS idx_ngo ON ekbd.ngo USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER ngo_mdate_update BEFORE UPDATE ON ekbd.ngo
    FOR EACH ROW
    EXECUTE PROCEDURE ekbd.f_update_mdate();

--ALTER TABLE IF EXISTS ekbd.ngo OWNER to ebd;
REVOKE ALL ON TABLE ekbd.ngo FROM ebd_integro;
GRANT ALL ON TABLE ekbd.ngo TO ebd;
GRANT SELECT ON TABLE ekbd.ngo TO ebd_integro;

COMMENT ON TABLE ekbd.ngo IS 'НГО';
COMMENT ON COLUMN ekbd.ngo.name IS 'Название';
COMMENT ON COLUMN ekbd.ngo.ngo_type_id IS 'Тип';
COMMENT ON COLUMN ekbd.ngo.ngp_id IS 'НГП';
COMMENT ON COLUMN ekbd.ngo.index_all IS 'Индекс';
COMMENT ON COLUMN ekbd.ngo.comment IS 'Примечание';
COMMENT ON COLUMN ekbd.ngo.geom IS 'Геометрия';
COMMENT ON COLUMN ekbd.ngo.cdate IS 'Дата создания';
COMMENT ON COLUMN ekbd.ngo.mdate IS 'Дата модификации/обновления';

END;