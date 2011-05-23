<?php
	class goodSearchZen extends base {
		
		function goodSearchZen() {
			if( !$this->checkActive() ) return false;
 			global $zco_notifier;
			$zco_notifier->attach($this, array('NOTIFY_SEARCH_ORDERBY_STRING'));
			$zco_notifier->attach($this, array('NOTIFY_SEARCH_SELECT_STRING'));
			$zco_notifier->attach($this, array('NOTIFY_SEARCH_WHERE_STRING'));
		}
		
		function update(&$class, $eventID) {
			global $order_str, $listing_sql, $select_str, $where_str, $keywords;

			switch( $eventID ) {					
				
				case 'NOTIFY_SEARCH_SELECT_STRING':
					// code bellow by Rob - www.funkyraw.com
					$robs_keywords=stripslashes($_GET['keyword']);
					$select_str .= ",  MATCH(pd.products_name) AGAINST('$robs_keywords') AS rank1, 
									MATCH(pd.products_description) AGAINST('$robs_keywords') AS rank2 ";
					// end of code by Rob
					break;
					
				// we assume search for products_id
/*
				case 'NOTIFY_SEARCH_WHERE_STRING':
					if(is_numeric($keywords)) $where_str = ' WHERE p.products_id=\''.$keywords.'\'';
					break;*/

					
				case 'NOTIFY_SEARCH_ORDERBY_STRING':
						
					$listing_sql = str_replace($order_str,
												' order by rank1 DESC, rank2 DESC, p.products_sort_order, pd.products_name',
												$listing_sql);
					break;	

			}
		}

		function checkActive() {
			global $db;
			$check_query = $db->Execute("SELECT * FROM " . TABLE_CONFIGURATION . 
    				" WHERE configuration_key='GOOD_SEARCH_ZEN_ACTIVE_STATE' AND configuration_value=1");
			return $check_query->RecordCount();
		}
	}                         
?>