--------------------------------------------------------------------------------------------
----------------------------		    	Триггеры		    	------------------------
--------------------------------------------------------------------------------------------



---------------------------
-- _mdate_update
--
-- при внесении изменений в запись обновляет значение mdate
-- (уже включены в скрипт создания таблицы)
---------------------------
	
	-- для Лицензий
CREATE OR REPLACE TRIGGER license_mdate_update BEFORE UPDATE ON ebd_ekbd.license
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();
	
	-- для Флангов
CREATE OR REPLACE TRIGGER flang_mdate_update BEFORE UPDATE ON ebd_ekbd.flang
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

		-- для Участки, предлагаемые к лицензированию
CREATE OR REPLACE TRIGGER konkurs_mdate_update BEFORE UPDATE ON ebd_ekbd.konkurs
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

	-- для Месторождения нефти и газа
CREATE OR REPLACE TRIGGER deposit_mdate_update BEFORE UPDATE ON ebd_ekbd.deposit
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

		-- для Нефтегазоперспективные структуры
CREATE OR REPLACE TRIGGER struct_mdate_update BEFORE UPDATE ON ebd_ekbd.struct
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

			-- для Особо охраняемые территории (заповедники) 
CREATE OR REPLACE TRIGGER zapovednik_mdate_update BEFORE UPDATE ON ebd_ekbd.zapovednik
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

				-- для НГО
CREATE OR REPLACE TRIGGER ngo_mdate_update BEFORE UPDATE ON ebd_ekbd.ngo
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

					-- для НГП
CREATE OR REPLACE TRIGGER ngp_mdate_update BEFORE UPDATE ON ebd_ekbd.ngp
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

					-- для НГР
CREATE OR REPLACE TRIGGER ngr_mdate_update BEFORE UPDATE ON ebd_ekbd.ngr
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

	

