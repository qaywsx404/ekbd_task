--------------------------------------------------------------------------------------------
----------------------------		    	Функции		    	----------------------------
--------------------------------------------------------------------------------------------



---------------------------
-- f_date_to_char(date)
--
--
-- иммутбл привидение даты к виду дд-мм-гггг
---------------------------
CREATE OR REPLACE FUNCTION  ebd_ekbd.f_date_to_char(date)
	RETURNS text AS
	$$ select to_char($1, 'DD-MM-YYYY'); $$
	LANGUAGE sql immutable;




---------------------------
-- f_update_mdate()
--
--
-- устанавливает новой записи mdate = now()
---------------------------
CREATE OR REPLACE FUNCTION  ebd_ekbd.f_update_mdate()
	RETURNS trigger AS
	$$
        BEGIN
            NEW.mdate := now();
            RETURN NEW;
        END;
    $$
	LANGUAGE plpgsql;







