<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migration - Crear vista SQL
     */
    public function up(): void
    {
        $sql = "
        CREATE OR REPLACE VIEW view_report_debts AS
        SELECT 
            `s`.`id` AS `id`,
            `s`.`full_name` AS `nombre_completo`,
            `s`.`document` AS `dni`,
            `s`.`email` AS `email`,
            `s`.`phone` AS `telefono`,
            `unificada`.`tipo_registro` AS `tipo_registro`,
            `unificada`.`entidad` AS `entidad`,
            `unificada`.`monto` AS `monto_deuda`,
            `unificada`.`situacion` AS `situacion`,
            `unificada`.`dias_vencimiento` AS `dias_vencimiento`,
            `cc`.`bank` AS `banco_tc`,
            `cc`.`line` AS `linea_credito_aprobada`,
            `cc`.`used` AS `linea_credito_utilizada`,
            `s`.`created_at` AS `fecha_creacion`,
            DATE_SUB(`s`.`created_at`, INTERVAL `unificada`.`dias_vencimiento` DAY) AS `fecha_suscripcion_real`
        FROM 
            `subscriptions` `s`
            JOIN `subscription_reports` `sr` ON `s`.`id` = `sr`.`subscription_id`
            JOIN (
                SELECT 
                    `report_loans`.`subscription_report_id` AS `subscription_report_id`,
                    'Prestamo' AS `tipo_registro`,
                    `report_loans`.`bank` AS `entidad`,
                    `report_loans`.`amount` AS `monto`,
                    `report_loans`.`status` AS `situacion`,
                    `report_loans`.`expiration_days` AS `dias_vencimiento`
                FROM `report_loans`
                
                UNION ALL
                
                SELECT 
                    `report_other_debts`.`subscription_report_id` AS `subscription_report_id`,
                    'Otras Deudas' AS `tipo_registro`,
                    `report_other_debts`.`entity` AS `entidad`,
                    `report_other_debts`.`amount` AS `monto`,
                    'Vigente' AS `situacion`,
                    `report_other_debts`.`expiration_days` AS `dias_vencimiento`
                FROM `report_other_debts`
            ) `unificada` ON `sr`.`id` = `unificada`.`subscription_report_id`
            LEFT JOIN `report_credit_cards` `cc` ON `sr`.`id` = `cc`.`subscription_report_id`
        ORDER BY 
            DATE_SUB(`s`.`created_at`, INTERVAL `unificada`.`dias_vencimiento` DAY) DESC;
        ";
        
        DB::statement($sql);
    }

    /**
     * Reverse the migration - Eliminar vista
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS view_report_debts');
    }
};