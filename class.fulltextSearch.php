<?php
	class fullTextSearch extends base {
		
		function fullTextSearch() {
 			global $zco_notifier;
			$zco_notifier->attach($this, array('NOTIFY_SEARCH_ORDERBY_STRING'));
			$zco_notifier->attach($this, array('NOTIFY_SEARCH_SELECT_STRING'));
			$zco_notifier->attach($this, array('NOTIFY_SEARCH_WHERE_STRING'));
		}
		
		function update(&$class, $eventID) {
			global $order_str, $listing_sql, $select_str, $db, $where_str, $keywords;
			
			switch( $eventID ) {									
				case 'NOTIFY_SEARCH_SELECT_STRING':
					// code bellow by Rob - www.funkyraw.com
					$robs_keywords=stripslashes($_GET['keyword']);
					$select_str .= ",  MATCH(pd.products_name) AGAINST('$robs_keywords') AS rank1, 
									MATCH(pd.products_description) AGAINST('$robs_keywords') AS rank2 ";
					// end of code by Rob
					break;					
				case 'NOTIFY_SEARCH_WHERE_STRING':
					$where_str .= ' OR (p.products_id=\''.$keywords.'\')';					
					break;
				case 'NOTIFY_SEARCH_ORDERBY_STRING':
	
					$this->checkIndexes();				
					$listing_sql = str_replace($order_str,
												' order by rank1 DESC, rank2 DESC, p.products_sort_order, pd.products_name',
												$listing_sql);
					die($listing_sql);
					break;	
			}
		}
		
		function checkIndexes() {
			global $db;
			// check name index
			$nameIndexSQL = "SHOW INDEX FROM ".TABLE_PRODUCTS_DESCRIPTION. 
		  	" WHERE Key_name = 'fulltextsearch_name' "; 
			$checkNameIndex = $db->Execute($nameIndexSQL);
			if( $checkNameIndex->RecordCount() == 0 ) {
				$createNameIndexSQL = "CREATE FULLTEXT INDEX fulltextsearch_name ON ".TABLE_PRODUCTS_DESCRIPTION."(products_name)";	
				$db->Execute($createNameIndexSQL);					
			}
			
			// check description index
			$descIndexSQL = "SHOW INDEX FROM ".TABLE_PRODUCTS_DESCRIPTION. 
						  	" WHERE Key_name = 'fulltextsearch_description' ";
			$checkDescIndex = $db->Execute($descIndexSQL);
			if( $checkDescIndex->RecordCount() == 0 ) {
				$createDescIndexSQL = "CREATE FULLTEXT INDEX fulltextsearch_description ON ".TABLE_PRODUCTS_DESCRIPTION."(products_description)";	
				$db->Execute($createDescIndexSQL);					
			}
		}
	}                         
?>