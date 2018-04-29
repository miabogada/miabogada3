<?php
/**
    * class extends WP_List_Table class, gets data from the table and creates a table with pagination according to the data.
    * 
    * 
    */
class WPRC_Repositories_List_Table extends WP_List_Table
{
    private $highlights=array();
    private $rtab='default';
    
	/**
    * method calls parent's construct with array parameters  
    * 
    */  
    function __construct() 
    {
          parent::__construct( array(
	      'plural' => 'list_repositories', //plural label, also this well be one of the table css class
          'singular'=> 'list_repository', //Singular label
	      'ajax'  => false //We won't support Ajax for this table
       ) );
        if (isset($_GET['rtab']) && $_GET['rtab']=='trash')
        {
            $this->rtab='trash';
        }
	}
    
    
 	function no_items() {
		_e( 'No repositories were found.','installer' );
	}
    
	/**
    * method overwrites WP_List_Table::get_columns() method and sets the names of the table fields 
    * 
    */ 
    function get_columns() 
    {
	    return $columns= array(
        'col_repository_name' => __('Name', 'installer'),
        'col_repository_endpoint_url' => __('End point url', 'installer'),
        'col_repository_enabled'=>__('Repository enabled', 'installer')
        );
    }
    
    /**
    * method sets the names of the sortable fields 
    * 
    */ 
    function get_sortable_columns() 
    {
	    return $sortable = array(
	        'col_repository_name'=>array('repository_name',true),
	        'col_repository_enabled'=>array('repository_enabled',true)
	    );
	}
    
    /**
    * method gets data to be display inside the table sets pagination data and sets items fields of the parent class 
    * 
    */
    function prepare_items()
    {
	    global $wpdb, $_wp_column_headers;
	    
        if ($this->rtab!='trash')
        {
            $screen = get_current_screen();
         
            /* -- Preparing query -- */
            $query = "SELECT * FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=0";
            $query2 = "SELECT count(*) FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=0";

            /* -- Ordering parameters -- */
                //Parameters that are going to be used to order the result
                $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
                $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
                if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
               
            /* -- Pagination parameters -- */
                //Number of elements in your table?
                $totalitems = $wpdb->get_var($query2); //return the total number of affected rows
                
                //How many to display per page?
                $perpage = 10;
                
                //Which page is this?
                $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
                
                //Page Number
                if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
                
                //How many pages do we have in total?
                $totalpages = ceil($totalitems/$perpage);
                
                //adjust the query to take pagination into account
                if(!empty($paged) && !empty($perpage)){
                    $offset=($paged-1)*$perpage;
                    $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
                }
         
            /* -- Register the pagination -- */
                $this->set_pagination_args( array(
                    "total_items" => $totalitems,
                    "total_pages" => $totalpages,
                    "per_page" => $perpage,
                ) );
                //The pagination links are automatically built according to those parameters
                
             /* — Register the Columns — */
                $columns = $this->get_columns();
                $hidden = array();
                $sortable = $this->get_sortable_columns();
                $this->_column_headers = array($columns, $hidden, $sortable);
                
             /* -- Fetch the items -- */
                $this->items = $wpdb->get_results($query);
                
                // get hightlighted repos
                $current = get_transient('wprc_update_repositories');
                if ($current!=false && isset($current) && is_object($current) && $current!='')
                {
                    foreach ($current->repos as $hightlightrepo)
                        $this->highlights[$hightlightrepo['url']]=1;
                }
            }
            else
            {
                $screen = get_current_screen();
             
                /* -- Preparing query -- */
                $query = "SELECT * FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=1";
                $query2 = "SELECT count(*) FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=1";

                /* -- Ordering parameters -- */
                    //Parameters that are going to be used to order the result
                    $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
                    $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
                    if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
                   
                /* -- Pagination parameters -- */
                    //Number of elements in your table?
                    $totalitems = $wpdb->get_var($query2); //return the total number of affected rows
                    
                    //How many to display per page?
                    $perpage = 10;
                    
                    //Which page is this?
                    $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
                    
                    //Page Number
                    if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
                    
                    //How many pages do we have in total?
                    $totalpages = ceil($totalitems/$perpage);
                    
                    //adjust the query to take pagination into account
                    if(!empty($paged) && !empty($perpage)){
                        $offset=($paged-1)*$perpage;
                        $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
                    }
             
                /* -- Register the pagination -- */
                    $this->set_pagination_args( array(
                        "total_items" => $totalitems,
                        "total_pages" => $totalpages,
                        "per_page" => $perpage,
                    ) );
                    //The pagination links are automatically built according to those parameters
                    
                 /* — Register the Columns — */
                    $columns = $this->get_columns();
                    $hidden = array();
                    $sortable = $this->get_sortable_columns();
                    $this->_column_headers = array($columns, $hidden, $sortable);
                    
                 /* -- Fetch the items -- */
                    $this->items = $wpdb->get_results($query);
            }
        }
    
	function get_views() {
		global $wpdb;

        $path = admin_url().'options-general.php?page='.WPRC_PLUGIN_FOLDER.'/pages/repositories.php';
        
        /* -- Preparing query -- */
        $query1 = "SELECT count(*) FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=0";
        $query2 = "SELECT count(*) FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=1";
        
        $active_count=$wpdb->get_var($query1);
        $trash_count=$wpdb->get_var($query2);
        
        $status_links = array();
        
        $class1=($this->rtab!='trash')?'class="current"':'';
        $class2=($this->rtab=='trash')?'class="current"':'';
        
        $status_links['active'] = sprintf('<a href="%1$s" %3$s>%2$s</a>' , $path, sprintf(__( 'Active <span class="count">(%s)</span>','installer'), number_format_i18n( $active_count ) ),$class1);
        $status_links['trash'] = sprintf('<a href="%1$s" %3$s>%2$s</a>' , $path.'&rtab=trash', sprintf(__( 'Trash <span class="count">(%s)</span>','installer'), number_format_i18n( $trash_count ) ),$class2);

		return $status_links;
	}
    /**
    * method forms the data output style 
    * 
    */
    function display_rows() 
    { 
	    if ($this->rtab!='trash')
        {
        $path = admin_url().'options-general.php?page='.WPRC_PLUGIN_FOLDER.'/pages/repositories.php';
        
        //Get the records registered in the prepare_items method
	    $records = $this->items;
        
	    //Get the columns registered in the get_columns and get_sortable_columns methods
	    list( $columns, $hidden ) = $this->get_column_info();
        
	    //Loop for each record
	    if(empty($records))
        {
            return false;   
        }
        
        $nonce_login = wp_create_nonce('installer-login-link');

        foreach($records as $rec)
        {
			//Open the line
	        $highlightclass="wprc-repository-hightlight";
			if (isset($this->highlights[$rec->repository_endpoint_url]))
				echo '<tr id="record_'.$rec->id.'" class="'.$highlightclass.'">';
			else
				echo '<tr id="record_'.$rec->id.'">';
            
	        foreach ( $columns as $column_name => $column_display_name ) {
	           //Style attributes for each col
	            $class = "class='$column_name column-$column_name'";
	            $style = "";
	            if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
	            $attributes = $class.$style;
	           
	            //Display the cell
	            switch ( $column_name ) {   
	                case "col_repository_name": 
                        $editlink = $path."&action=edit&amp;id=$rec->id";
                        
                        echo '<td '.$attributes.'><strong><a href="'.$editlink.'" title="Edit">'.stripslashes($rec->repository_name).'</a></strong>';
                        $actions = array();
                        $actions['edit'] = '<a class="submitedit" href="'.$editlink.'" title="'.__('Edit','installer').'">'.__('Edit','installer').'</a>';
						$actions['delete'] = "<a class='submitdelete' href='".wp_nonce_url( $path."&action=delete&amp;id=$rec->id", 'delete-repository_'.$rec->id )."' onclick=\"if(confirm('".esc_js( sprintf( __( "Are you sure that you want to delete this repository '%s'?\n\n Click 'Cancel' to stop, 'OK' to delete.", 'installer' ), $rec->repository_name ) ) . "' ) ) { return true;}return false;\">" . __( 'Delete', 'installer') . "</a>";
                        if((empty($rec->repository_username) && empty($rec->repository_password)) && $rec->repository_endpoint_url!=WPRC_WP_PLUGINS_REPO && $rec->repository_endpoint_url!=WPRC_WP_THEMES_REPO){
                            
                            $actions['login'] = '<a href=" ' . admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin&amp;repository_id=' . $rec->id.'&amp;_wpnonce='.$nonce_login) . '" class="thickbox" title="' . __('Log in', 'installer') . '">' . __('Login' , 'installer') . '</a>';
                        }
						echo $this->row_actions( $actions );
						echo '</td>';
                        break;
                        
	                case "col_repository_endpoint_url": 
                        echo '<td '.$attributes.'>'.stripslashes($rec->repository_endpoint_url).'</td>'; 
                        break;

	                case "col_repository_enabled": 
                        if($rec->repository_enabled)
                        {
                            $repository_enabled_caption = __('Yes', 'installer');
                        }
                        else
                        {
                            $repository_enabled_caption = __('No', 'installer');
                        }
                        echo '<td '.$attributes.'>'.$repository_enabled_caption.'</td>'; 
                        break;
	            }
	        }
	        echo'</tr>';
            
	    }
        }
        else
        {
            $path = admin_url().'options-general.php?page='.WPRC_PLUGIN_FOLDER.'/pages/repositories.php&rtab=trash';
            
            //Get the records registered in the prepare_items method
            $records = $this->items;
            
            //Get the columns registered in the get_columns and get_sortable_columns methods
            list( $columns, $hidden ) = $this->get_column_info();
            
            //Loop for each record
            if(empty($records))
            {
                return false;   
            }
            
            foreach($records as $rec)
            {
                //Open the line
                echo '<tr id="record_'.$rec->id.'">';
                
                foreach ( $columns as $column_name => $column_display_name ) {
                   //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                    $attributes = $class.$style;
                   
                    //Display the cell
                    switch ( $column_name ) {   
                        case "col_repository_name": 
                            echo '<td '.$attributes.'><strong>'.stripslashes($rec->repository_name).'</strong>';
                            $actions = array();
                            $actions['undelete'] = "<a class='submitundelete' href='".wp_nonce_url( $path."&action=undelete&amp;id=$rec->id", 'undelete-repository_'.$rec->id )."' onclick=\"if(confirm('".esc_js( sprintf( __( "Are you sure that you want to undelete this repository '%s'?\n\n Click 'Cancel' to stop, 'OK' to undelete.", 'installer' ), $rec->repository_name ) ) . "' ) ) { return true;}return false;\">" . __( 'UnDelete', 'installer') . "</a>";
                            echo $this->row_actions( $actions );
                            echo '</td>';
                            break;
                            
                        case "col_repository_endpoint_url": 
                            echo '<td '.$attributes.'>'.stripslashes($rec->repository_endpoint_url).'</td>'; 
                            break;

                        case "col_repository_enabled": 
                            if($rec->repository_enabled)
                            {
                                $repository_enabled_caption = __('Yes', 'installer');
                            }
                            else
                            {
                                $repository_enabled_caption = __('No', 'installer');
                            }
                            echo '<td '.$attributes.'>'.$repository_enabled_caption.'</td>'; 
                            break;
                    }
                }
                echo'</tr>';
                
            }
        }
    
     }  
     

}

?>