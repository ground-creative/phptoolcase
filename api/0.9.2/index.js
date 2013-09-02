var index =
[
    [ "Database Connection Manager (PtcDb)", "connection_manager.html", [
      [ "Introduction", "connection_manager.html#introduction", null ],
      [ "Getting Started", "connection_manager.html#db_getting_started", null ],
      [ "Connection Options", "connection_manager.html#connection_options", [
        [ "name", "connection_manager.html#connectionName", null ],
        [ "driver", "connection_manager.html#connectionDriver", null ],
        [ "user", "connection_manager.html#connectionUser", null ],
        [ "pass", "connection_manager.html#connectionPass", null ],
        [ "host", "connection_manager.html#connectionHost", null ],
        [ "db", "connection_manager.html#connectionDb", null ],
        [ "charset", "connection_manager.html#connectionCharset", null ],
        [ "query_builder", "connection_manager.html#connectionQueryBuilder", null ],
        [ "query_builder_class", "connection_manager.html#connectionQueryBuilderClass", null ],
        [ "pdo_attributes", "connection_manager.html#connectionPdoAttributes", null ]
      ] ],
      [ "Using Pdo with the Connection Manager", "connection_manager.html#usingPdo", null ],
      [ "Using the Query Builder", "connection_manager.html#usingQueryBuilder", null ],
      [ "Getting Connection Details", "connection_manager.html#connectionDetails", null ]
    ] ],
    [ "Query Builder Component (PtcQueryBuilder)", "query_builder.html", [
      [ "Introduction", "query_builder.html#introduction", null ],
      [ "Getting Started", "query_builder.html#qb_getting_started", null ],
      [ "Selecting Data", "query_builder.html#selecting_data", [
        [ "Retrieving Records From A Table", "query_builder.html#selecting_all_rows", null ],
        [ "Retrieving 1 Record From A Table", "query_builder.html#qb_selecting_one_row", null ],
        [ "Retrieving 1 Column From A Record", "query_builder.html#selecting_one_column", null ],
        [ "Specifying Columns To Be Selected", "query_builder.html#qb_specify_column", null ],
        [ "Adding Multiple Tables", "query_builder.html#qb_multiple_tables", null ]
      ] ],
      [ "Where Operators", "query_builder.html#qb_where_operators", [
        [ "Where Or", "query_builder.html#where_or", null ],
        [ "Where In", "query_builder.html#where_in", null ],
        [ "Where Between", "query_builder.html#where_between", null ],
        [ "Using Closures With Where", "query_builder.html#complex_where", null ]
      ] ],
      [ "Joins", "query_builder.html#qb_joins", [
        [ "Simple Join", "query_builder.html#simple_join", null ],
        [ "Using Closures With Joins", "query_builder.html#complex_joins", null ]
      ] ],
      [ "Group, Order And Limit", "query_builder.html#qb_order_group_limit", null ],
      [ "Manipulating Data", "query_builder.html#manipulating_data", [
        [ "Insert Recods", "query_builder.html#qb_insert_data", null ],
        [ "Update Records", "query_builder.html#qb_update_data", null ],
        [ "Delete Records", "query_builder.html#qb_delete_data", null ],
        [ "Last Inserted Id", "query_builder.html#qb_last_insert_id", null ],
        [ "Counting Rows", "query_builder.html#qb_count_rows", null ]
      ] ],
      [ "Raw Statements", "query_builder.html#qb_rawStatemet", null ],
      [ "Raw Values", "query_builder.html#qb_rawValues", null ],
      [ "Preparing Queries", "query_builder.html#qb_preparing_queries", null ],
      [ "Setting The Fetch Mode", "query_builder.html#set_fetch_mode", null ],
      [ "Specifying The Return Type", "query_builder.html#specifying_return", null ],
      [ "Observer Events", "query_builder.html#query_event", null ]
    ] ],
    [ "Object Relational Mapping Component (PtcMapper)", "object_relational_mapping.html", [
      [ "Introduction", "object_relational_mapping.html#introduction", null ],
      [ "Getting Started", "object_relational_mapping.html#getting_started", null ],
      [ "Specifying Table Name", "object_relational_mapping.html#specify_table", null ],
      [ "Retrieve All Records", "object_relational_mapping.html#retrieve_records", null ],
      [ "Retrieve Record Based On Id", "object_relational_mapping.html#retrieve_record_by_id", null ],
      [ "Using The Query Builder Directly", "object_relational_mapping.html#using_query_builder", null ],
      [ "Adding Records", "object_relational_mapping.html#adding_record", null ],
      [ "Updating Records", "object_relational_mapping.html#update_record", null ],
      [ "Deleting Records", "object_relational_mapping.html#delete_record", null ],
      [ "Converting Values", "object_relational_mapping.html#convert_values", [
        [ "Convert Values To Array", "object_relational_mapping.html#convert_to_array", null ],
        [ "Convert Values To Json", "object_relational_mapping.html#convert_to_json", null ]
      ] ],
      [ "Retrieving Single Values", "object_relational_mapping.html#retrieve_single_value", null ],
      [ "Updating Single Values", "object_relational_mapping.html#update_single_value", null ],
      [ "Mapping Field Names", "object_relational_mapping.html#mapping_fields", null ],
      [ "Using Observer Events", "object_relational_mapping.html#using_observers", null ],
      [ "Adding Options On Initialization", "object_relational_mapping.html#add_opts_on_initliaz", null ],
      [ "Specify Connection To Use", "object_relational_mapping.html#change_connection", null ],
      [ "Specify Unique Key", "object_relational_mapping.html#specifyUniqueKey", null ],
      [ "Specify Event Class", "object_relational_mapping.html#specifyEventClass", null ],
      [ "Specify Connection Manager Class", "object_relational_mapping.html#specifyConnectionManagerClass", null ],
      [ "Adding Options On Initialization", "object_relational_mapping.html#using_boot", null ]
    ] ],
    [ "Event Dispatcher Component (PtcEvent)", "event_dispatcher.html", [
      [ "Introduction", "event_dispatcher.html#introduction", null ],
      [ "Getting Started", "event_dispatcher.html#getting_started", null ],
      [ "Adding Event Listeners", "event_dispatcher.html#adding_events", [
        [ "Adding Closures as Listeners", "event_dispatcher.html#basic_usage", null ],
        [ "Adding Declared Functions As Listeners", "event_dispatcher.html#adding_functions", null ],
        [ "Adding Classes As Listeners", "event_dispatcher.html#using_classes", null ],
        [ "Using Wildcards", "event_dispatcher.html#using_wildcards", null ],
        [ "Assigning Priority", "event_dispatcher.html#using_priority", null ]
      ] ],
      [ "Firing Events", "event_dispatcher.html#firing_events", null ],
      [ "Preventing Event Propagation", "event_dispatcher.html#prevent_propagation", null ],
      [ "Retrieving Previously Added Events", "event_dispatcher.html#getting_events", null ],
      [ "Removing Event Listeners", "event_dispatcher.html#removing_events", null ],
      [ "Using The Library Helpers", "event_dispatcher.html#register_component", null ]
    ] ],
    [ "Autoloader / HandyMan Component (PtcHandyMan)", "handyman.html", [
      [ "Introduction", "handyman.html#introduction", null ],
      [ "Getting Started", "handyman.html#hm_getting_started", null ],
      [ "Pointing The Autoloader To Files", "handyman.html#adding_dirs", [
        [ "Adding Files", "handyman.html#add_files", null ],
        [ "Adding Directories", "handyman.html#add_dirs", null ],
        [ "Adding Namespace Directories", "handyman.html#namespace_dirs", null ],
        [ "Using Separators", "handyman.html#using_separators", null ]
      ] ],
      [ "Retrieving Autoloader Directories", "handyman.html#getDirs", null ],
      [ "Using The Application Paths Manager", "handyman.html#configure_app", [
        [ "Adding Paths", "handyman.html#addingAppPath", null ],
        [ "Retrieving Added Paths", "handyman.html#usingAddedPath", null ]
      ] ],
      [ "Reading Inaccessible Properties", "handyman.html#read_properties", null ],
      [ "Using The Library Helpers", "handyman.html#using_helpers", null ]
    ] ],
    [ "Debugger & Logger Component (PtcDebug)", "debugger_logger.html", [
      [ "Introduction", "debugger_logger.html#introduction", null ],
      [ "Getting Started", "debugger_logger.html#dbg_getting_started", null ],
      [ "Class Options", "debugger_logger.html#dbg_class_options", [
        [ "url_key", "debugger_logger.html#url_key", null ],
        [ "url_pass", "debugger_logger.html#url_pass", null ],
        [ "replace_error_handler", "debugger_logger.html#replace_error_handler", null ],
        [ "error_reporting", "debugger_logger.html#error_reporting", null ],
        [ "catch_exceptions", "debugger_logger.html#catch_exceptions", null ],
        [ "check_referer", "debugger_logger.html#check_referer", null ],
        [ "die_on_error", "debugger_logger.html#die_on_error", null ],
        [ "debug_console", "debugger_logger.html#debug_console", null ],
        [ "allowed_ips", "debugger_logger.html#allowed_ips", null ],
        [ "session_start", "debugger_logger.html#session_start", null ],
        [ "show_interface", "debugger_logger.html#show_interface", null ],
        [ "set_time_limit", "debugger_logger.html#set_time_limit", null ],
        [ "memory_limit", "debugger_logger.html#memory_limit", null ],
        [ "show_messages", "debugger_logger.html#show_messages", null ],
        [ "show_globals", "debugger_logger.html#show_globals", null ],
        [ "show_sql", "debugger_logger.html#show_sql", null ],
        [ "show_w3c", "debugger_logger.html#show_w3c", null ],
        [ "minified_html", "debugger_logger.html#minified_html", null ],
        [ "trace_depth", "debugger_logger.html#trace_depth", null ],
        [ "max_dump_depth", "debugger_logger.html#max_dump_depth", null ],
        [ "panel_top", "debugger_logger.html#panel_top", null ],
        [ "panel_right", "debugger_logger.html#panel_right", null ],
        [ "default_category", "debugger_logger.html#default_category", null ],
        [ "enable_inspector", "debugger_logger.html#enable_inspector", null ],
        [ "code_coverage", "debugger_logger.html#code_coverage", null ],
        [ "trace_functions", "debugger_logger.html#trace_functions", null ],
        [ "exclude_categories", "debugger_logger.html#exclude_categories", null ]
      ] ],
      [ "Logging Data", "debugger_logger.html#logging_data", [
        [ "Logging Messages", "debugger_logger.html#log_msg", null ],
        [ "Logging Sql Queries", "debugger_logger.html#log_sql", null ]
      ] ],
      [ "Execution Timing", "debugger_logger.html#execution_timing", [
        [ "Timing Loops", "debugger_logger.html#timing_code", null ],
        [ "Timing Sql Queries", "debugger_logger.html#timing_sql", null ]
      ] ],
      [ "Replacing Error Handler", "debugger_logger.html#replaceErrorHandler", null ],
      [ "Inspecting Variable Changes", "debugger_logger.html#variableInspector", null ],
      [ "Code Coverage Analysis", "debugger_logger.html#codeCoverage", null ],
      [ "Function Calls Tracing", "debugger_logger.html#traceFunctions", null ],
      [ "Attaching Data To Messages", "debugger_logger.html#add_to_log", null ],
      [ "Inspecting Source Code", "debugger_logger.html#file_inspector", null ],
      [ "Searching For A String Recursively", "debugger_logger.html#search_string", null ],
      [ "Hiding Panels", "debugger_logger.html#hiding_panels", null ],
      [ "Setup Examples", "debugger_logger.html#Typical", [
        [ "Development / Testing Environment", "debugger_logger.html#develop_env", null ],
        [ "Production Environment", "debugger_logger.html#prod_env", null ],
        [ "Ajax Debugging", "debugger_logger.html#ajax_env", null ]
      ] ]
    ] ],
    [ "Dynamic HTML Form Generator & Validator (PtcForm)", "dynamic_form.html", [
      [ "Introduction", "dynamic_form.html#introduction", null ],
      [ "Getting Started", "dynamic_form.html#getting_started", null ],
      [ "Class Options", "dynamic_form.html#dyn_class_options", [
        [ "form_method", "dynamic_form.html#form_method", null ],
        [ "form_action", "dynamic_form.html#form_action", null ],
        [ "form_width", "dynamic_form.html#form_width", null ],
        [ "add_class_validator", "dynamic_form.html#add_class_validator", null ],
        [ "labels_align", "dynamic_form.html#labels_align", null ],
        [ "labels_width", "dynamic_form.html#labels_width", null ],
        [ "style_elements", "dynamic_form.html#style_elements", null ],
        [ "style_labels", "dynamic_form.html#style_labels", null ],
        [ "style_tables", "dynamic_form.html#style_tables", null ],
        [ "spacer_height", "dynamic_form.html#spacer_height", null ],
        [ "keep_values", "dynamic_form.html#keep_values", null ],
        [ "print_form", "dynamic_form.html#print_form", null ],
        [ "start_tab", "dynamic_form.html#start_tab", null ],
        [ "err_msg_level", "dynamic_form.html#err_msg_level", null ],
        [ "default_category", "dynamic_form.html#default_category", null ],
        [ "event_class", "dynamic_form.html#event_class", null ]
      ] ],
      [ "Field Parameters", "dynamic_form.html#fieldParams", [
        [ "name", "dynamic_form.html#name", null ],
        [ "type", "dynamic_form.html#type", null ],
        [ "label", "dynamic_form.html#label", null ],
        [ "attributes", "dynamic_form.html#attributes", null ],
        [ "labelOptions", "dynamic_form.html#labelOptions", null ],
        [ "parentEl", "dynamic_form.html#parentEl", null ],
        [ "events", "dynamic_form.html#events", null ],
        [ "validate", "dynamic_form.html#validate", null ],
        [ "values", "dynamic_form.html#values", null ],
        [ "value", "dynamic_form.html#value", null ]
      ] ],
      [ "Adding Fields Examples", "dynamic_form.html#adding_fields", [
        [ "Adding Text Fields", "dynamic_form.html#add_textfield", null ],
        [ "Adding Select Fields", "dynamic_form.html#add_select", null ],
        [ "Adding Checkboxgroups / Radiogroups", "dynamic_form.html#add_checkbox", null ],
        [ "Adding Composite Fields", "dynamic_form.html#add_compo", null ],
        [ "Adding Custom Fields", "dynamic_form.html#add_custom", null ]
      ] ],
      [ "Adding Html Attributes To Elements", "dynamic_form.html#adding_html_attributes", [
        [ "Adding Attributes To Fields", "dynamic_form.html#adding_html_att", null ],
        [ "Adding Attributes To Label Containers", "dynamic_form.html#adding_label_att", null ],
        [ "Adding Attributes To Field Containers", "dynamic_form.html#add_container_att", null ],
        [ "Extending The Compiler With Custom Attributes", "dynamic_form.html#extend_att", null ]
      ] ],
      [ "Changing Default Styles Applied To Elements", "dynamic_form.html#change_default_styles", [
        [ "Changing default styles for fields", "dynamic_form.html#change_style_fields", null ],
        [ "Changing Default Styles For Label Containers", "dynamic_form.html#change_style_labels", null ]
      ] ],
      [ "Using Closures With Field Parameters", "dynamic_form.html#using_closures", null ],
      [ "Manipulating Html Templates", "dynamic_form.html#manipulate_templates", null ],
      [ "Adding Values To Fields", "dynamic_form.html#adding_values", [
        [ "Adding Values As A String", "dynamic_form.html#add_values_string", null ],
        [ "Adding Values As Array (select, radiogroup, checkboxgroup)", "dynamic_form.html#add_values_array", null ],
        [ "Adding Fields As Array Of Values (composite, fieldset)", "dynamic_form.html#adding_values_fields", null ]
      ] ],
      [ "Using [brackets] To Add Parameters To Field Values", "dynamic_form.html#using_brackets", null ],
      [ "Adding JavScript Events", "dynamic_form.html#add_events", null ],
      [ "Rendering The Form", "dynamic_form.html#render_form", null ],
      [ "Adding & Validating Fields With The Validator Engine", "dynamic_form.html#adding_validation", [
        [ "Adding Fields To The Validator", "dynamic_form.html#add_fields_validator", null ],
        [ "Validating The Form (Server Side)", "dynamic_form.html#validate_form", null ]
      ] ],
      [ "Using Observer Events", "dynamic_form.html#using_observer", null ],
      [ "Adding Options On Initialization", "dynamic_form.html#using_boot", null ],
      [ "Adding Form Fields On Initialization", "dynamic_form.html#formFields", null ]
    ] ],
    [ "Helper Functions (ptc-helpers.php)", "helper_functions.html", [
      [ "Introduction", "helper_functions.html#introduction", null ],
      [ "Getting Started", "helper_functions.html#getting_started", null ],
      [ "Helpers Reference", "helper_functions.html#helpers_ref", [
        [ "PtcDebug Helpers", "helper_functions.html#ptcdebug_helpers", null ],
        [ "PtcHandyMan Helpers", "helper_functions.html#ptchm_helpers", null ],
        [ "PtcEvent Helpers", "helper_functions.html#ptcevent_helpers", null ]
      ] ]
    ] ]
];