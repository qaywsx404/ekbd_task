------------------------------------------------------------------------------------------------
----------------------------			Сущность: НГО			----------------------------
------------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ebd_ekbd.ngo CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ebd_ekbd.ngo (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid, 	

	name character varying,
    ngo_type_id uuid,
    ngp_id	uuid DEFAULT NULL,
    index_all character varying,
    				
	comment character varying DEFAULT NULL,
	src_hash character varying GENERATED ALWAYS AS (md5(
                                                    (
                                                    COALESCE(name, '') || COALESCE(ngo_type_id::text, '') || COALESCE(ngp_id::text, '')
                                                    || COALESCE(index_all, '') || COALESCE(geom::text, '')
                                                    )::text
									                ))
                                                    STORED NOT NULL,
	cdate timestamp DEFAULT now(), 				
    mdate timestamp DEFAULT now(),
	geom geometry(MultiPolygon, 987654),

	CONSTRAINT ngo_pkey PRIMARY KEY (id),
    CONSTRAINT ngo_vid_fkey FOREIGN KEY (vid) REFERENCES ebd_ekbd.ngo,  
	CONSTRAINT ngo_dic_ngo_type_fkey FOREIGN KEY (ngo_type_id) REFERENCES ebd_ekbd.dic_ngo_type(id),
	CONSTRAINT ngo_ngp_fkey FOREIGN KEY (ngp_id) REFERENCES ebd_ekbd.ngp(id)
);

CREATE INDEX IF NOT EXISTS idx_ngo ON ebd_ekbd.ngo USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER ngo_mdate_update BEFORE UPDATE ON ebd_ekbd.ngo
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

ALTER TABLE IF EXISTS ebd_ekbd.ngo OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.ngo FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.ngo TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.ngo TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.ngo IS 'НГО';
COMMENT ON COLUMN ebd_ekbd.ngo.name IS 'Название';
COMMENT ON COLUMN ebd_ekbd.ngo.ngo_type_id IS 'Тип';
COMMENT ON COLUMN ebd_ekbd.ngo.ngp_id IS 'НГП';
COMMENT ON COLUMN ebd_ekbd.ngo.index_all IS 'Индекс';
COMMENT ON COLUMN ebd_ekbd.ngo.comment IS 'Примечание';
COMMENT ON COLUMN ebd_ekbd.ngo.geom IS 'Геометрия';
COMMENT ON COLUMN ebd_ekbd.ngo.cdate IS 'Дата создания';
COMMENT ON COLUMN ebd_ekbd.ngo.mdate IS 'Дата модификации/обновления';

END;