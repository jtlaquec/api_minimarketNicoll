<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->actualizarCostoUnitario();
        $this->actualizarEstado();
        $this->rolesDetalle();
        $this->detallePermiso();
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $this->eliminar_actualizarCostoUnitario();
    }

    private function actualizarCostoUnitario(){
        DB::unprepared('CREATE TRIGGER calcular_costo_unitario
        BEFORE INSERT ON inventarios
        FOR EACH ROW
    BEGIN
        -- Calcular la sumatoria de cantidad y sumatoria de cantidad por compra antes de la inserción
        DECLARE sum_cantidad DECIMAL(10, 2);
        DECLARE sum_cantidad_compra DECIMAL(10, 2);
        DECLARE new_sum_cantidad DECIMAL(10, 2);
        DECLARE new_sum_cantidad_compra DECIMAL(10, 2);
        DECLARE new_costo_unitario DECIMAL(10, 2);
        DECLARE precio_compra DECIMAL(10, 2);
    
        IF NEW.tipo = 1 THEN
            SELECT SUM(CASE WHEN tipo = 1 THEN cantidad ELSE -cantidad END),
                   SUM(CASE WHEN tipo = 1 THEN cantidad * costoUnitario ELSE -cantidad * costoUnitario END)
            INTO sum_cantidad, sum_cantidad_compra
            FROM inventarios
            WHERE estado = 1 AND producto_id = NEW.producto_id;
    
            IF sum_cantidad_compra IS NULL THEN
                SET sum_cantidad_compra = 0;
            END IF;
    
            IF sum_cantidad_compra = 0 THEN
                UPDATE productos SET precioCompra = NEW.costoUnitario WHERE id = NEW.producto_id;
            ELSE
                SET new_sum_cantidad = sum_cantidad + NEW.cantidad;
                SET new_sum_cantidad_compra = sum_cantidad_compra + (NEW.cantidad * NEW.costoUnitario);
    
                IF new_sum_cantidad = 0 THEN
                    -- Handle division by zero error
                    SET new_costo_unitario = 0; -- Set a default value or handle the case accordingly
                ELSE
                    SET new_costo_unitario = new_sum_cantidad_compra / new_sum_cantidad;
                END IF;
    
                -- Actualizar la tabla productos
                UPDATE productos SET precioCompra = new_costo_unitario WHERE id = NEW.producto_id;
            END IF;
        END IF;
    
        IF NEW.tipo = 2 THEN
            SELECT precioCompra INTO precio_compra FROM productos WHERE id = NEW.producto_id;
            SET NEW.costoUnitario = precio_compra;
        END IF;
    END');
    }
    private function eliminar_actualizarCostoUnitario(){

    }

    private function actualizarEstado(){
        DB::unprepared('CREATE TRIGGER actualizarCostoEstado
        AFTER UPDATE ON inventarios
        FOR EACH ROW
        BEGIN
        -- Variables para el cálculo del costo unitario
        DECLARE new_sum_cantidad DECIMAL(10, 2);
        DECLARE new_sum_cantidad_compra DECIMAL(10, 2);
        DECLARE new_costo_unitario DECIMAL(10, 2);
    
        -- Calcular la sumatoria de cantidad y sumatoria de cantidad por compra después de la actualización
        SELECT SUM(CASE WHEN tipo = 1 THEN cantidad ELSE -cantidad END),
               SUM(CASE WHEN tipo = 1 THEN cantidad * costoUnitario ELSE -cantidad * costoUnitario END)
        INTO new_sum_cantidad, new_sum_cantidad_compra
        FROM inventarios
        WHERE estado = 1 AND producto_id = OLD.producto_id;
    
        -- Actualizar el costo unitario solo si el estado cambia de 0 a 1 o de 1 a 0
        IF NEW.estado <> OLD.estado THEN
            IF new_sum_cantidad = 0 THEN
                -- Handle division by zero error
                SET new_costo_unitario = 0; -- Set a default value or handle the case accordingly
            ELSE
                SET new_costo_unitario = new_sum_cantidad_compra / new_sum_cantidad;
            END IF;
    
            -- Actualizar el costo unitario y el estado del producto
            UPDATE productos 
            SET precioCompra = new_costo_unitario,
                stock = stock + (CASE 
                    WHEN NEW.estado = 1 THEN
                        CASE WHEN OLD.tipo = 1 THEN NEW.cantidad ELSE -NEW.cantidad END
                    ELSE
                        CASE WHEN OLD.tipo = 1 THEN -OLD.cantidad ELSE OLD.cantidad END
                    END)
            WHERE id = OLD.producto_id;
        END IF;
    END');
    }

    public function detallePermiso(){
        DB::unprepared('CREATE TRIGGER agregar_permiso_roles AFTER INSERT ON permisos
        FOR EACH ROW
        BEGIN
            DECLARE role_id INT;
            DECLARE done INT DEFAULT FALSE;
            DECLARE cur CURSOR FOR SELECT id FROM roles;
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        
            SET @current_date = NOW();
        
            -- Verificar si hay roles existentes
            SELECT COUNT(*) INTO @num_roles FROM roles;
        
            IF @num_roles > 0 THEN
                OPEN cur;
                read_loop: LOOP
                    FETCH cur INTO role_id;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;
        
                    INSERT INTO detalle_permisos (role_id, permiso_id, created_at, updated_at)
                    VALUES (role_id, NEW.id, @current_date, @current_date);
                END LOOP;
                CLOSE cur;
            END IF;
        END
        
        
        
        
        
        
        ');
    }


    public function rolesDetalle(){
        DB::unprepared('CREATE TRIGGER agregar_permisos_nuevo_rol AFTER INSERT ON roles
        FOR EACH ROW
        BEGIN
            DECLARE permiso_id INT;
            DECLARE done INT DEFAULT FALSE;
            DECLARE cur CURSOR FOR SELECT id FROM permisos;
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        
            SET @current_date = NOW();
        
            -- Verificar si hay permisos existentes
            SELECT COUNT(*) INTO @num_permisos FROM permisos;
        
            IF @num_permisos > 0 THEN
                OPEN cur;
                read_loop: LOOP
                    FETCH cur INTO permiso_id;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;
        
                    INSERT INTO detalle_permisos (role_id, permiso_id, created_at, updated_at)
                    VALUES (NEW.id, permiso_id, @current_date, @current_date);
                END LOOP;
                CLOSE cur;
            END IF;
        END
        
        
        
        
        
        
        ');
    }


};
