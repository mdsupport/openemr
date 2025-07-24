--
-- Add LBFptlists
--

-- Inserting encisscats list
INSERT INTO list_options (`list_id`,`option_id`,`title`) VALUES ('lists','encisscats','Issue Types for encounter patient data');
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, notes ) VALUES ('encisscats', 'medication', 'Medications', 1, 0, 'N');

-- Inserting LBFptlists
INSERT INTO `layout_group_properties` (`grp_form_id`, `grp_group_id`, `grp_title`, `grp_subtitle`, `grp_mapping`, `grp_seq`, `grp_activity`, `grp_repeats`, `grp_columns`, `grp_size`, `grp_issue_type`, `grp_aco_spec`, `grp_services`, `grp_products`, `grp_diags`, `grp_save_close`, `grp_init_open`, `grp_last_update`, `grp_referrals`) VALUES ('LBFptlists', '', 'Patient Lists', '', 'Patient Details', 0, 1, 0, 2, 9, '', '', '', '', '', 0, 0, '2020-01-01 00:00:01', 0);
INSERT INTO `layout_group_properties` (`grp_form_id`, `grp_group_id`, `grp_title`, `grp_subtitle`, `grp_mapping`, `grp_seq`, `grp_activity`, `grp_repeats`, `grp_columns`, `grp_size`, `grp_issue_type`, `grp_aco_spec`, `grp_services`, `grp_products`, `grp_diags`, `grp_save_close`, `grp_init_open`, `grp_last_update`, `grp_referrals`) VALUES ('LBFptlists', '1', 'Active', '', '', 0, 1, 0, 0, 0, '', '', '', '', '', 0, 0, NULL, 0);
INSERT INTO `layout_options` (`form_id`, `field_id`, `group_id`, `group_name`, `title`, `seq`, `data_type`, `uor`, `fld_length`, `max_length`, `list_id`, `titlecols`, `datacols`, `default_value`, `edit_options`, `description`, `fld_rows`, `list_backup_id`, `source`, `conditions`, `validation`, `codes`) VALUES ('LBFptlists', 'medication', '1', '', 'Medications', 10, 3, 1, 80, 0, '', 1, 1, '', '', '', 0, '', 'F', '', '', '');
