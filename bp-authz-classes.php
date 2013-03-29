<?php
/**
 * BP_Authz_ACL Classes
 *
 * @package BP-Privacy
 * @author Jeff Sayre
 * @copyright Copyright 2009 - 2011 Jeff Sayre and SayreMedia, Inc
 */

/**
 * The main BPAz ACL table which holds the specific ACL values for each filtered item.
 * Data is stored in a normalized structure.
 *
 * @package BP-Privacy Classes
 * @author Jeff Sayre
 * @since 1.0-RC1
 * @version 1.0
 */
class BP_Authz_ACL_Main {

	// Field declarations for xx_bp_authz_acl_main table
	public $id;
	public $user_id;
	public $filtered_component;
	public $filtered_item;
	public $item_id;
	public $bpaz_level;
	public $last_updated;

	/* Note: along with the public class properties listed above, the nested array
	 * $group_user_list_id_array is passed into BP_Authz_ACL_Main::save method
	 * and is used in the ACL Lists table save method if data exists in array.
	 */

	public function __construct( $id = null, $user_id = null, $filtered_component = null ) {

		if ( $id ) {
			$this->id = $id;
			$this->user_id = $user_id;
			$this->filtered_component = $filtered_component;
			$this->populate( $this->id, $this->user_id, $this->filtered_component );
		}
	}

	private function populate( $id, $user_id, $filtered_component ) {
		global $wpdb, $bp, $userdata;

		if ( is_null($user_id) ) {
			$user_id = $userdata->ID;
		}

		if ( $acl_main = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->authz->table_name_acl_main} WHERE id = %d", $this->id ) ) ) {
			$this->id = $acl_main->id;
			$this->user_id = $acl_main->user_id;
			$this->filtered_component = stripslashes($acl_main->filtered_component);
			$this->filtered_item = stripslashes($acl_main->filtered_item);
			$this->item_id = $acl_main->item_id;
			$this->bpaz_level = $acl_main->bpaz_level;
			$this->last_updated = $acl_main->last_updated;
		}

	}

	function save() {
		global $wpdb, $bp;

		// Pre-save filter hooks
		$this->user_id = apply_filters( 'authz_acl_user_id_before_save', $this->user_id, $this->id );
		$this->filtered_component = apply_filters( 'authz_acl_filtered_component_before_save', $this->filtered_component, $this->id );
		$this->filtered_item = apply_filters( 'authz_acl_filtered_item_before_save', $this->filtered_item, $this->id );
		$this->item_id = apply_filters( 'authz_acl_filtered_item_id_before_save', $this->item_id, $this->id );
		$this->bpaz_level = apply_filters( 'authz_acl_bpaz_level_before_save', $this->bpaz_level, $this->id );
		$this->last_updated = apply_filters( 'authz_acl_last_updated_before_save', date( 'Y-m-d H:i:s' ), $this->id );

		// Pre-save action hook
		do_action( 'authz_acl_main_before_save', $this );

		if ( $this->id ) {
			// We're dealing with an existing record so update
			$sql = $wpdb->prepare(
				"UPDATE {$bp->authz->table_name_acl_main} SET
					user_id = %d,
					filtered_component = %s,
					filtered_item = %s,
					item_id = %d,
					bpaz_level = %d,
					last_updated = %s
				WHERE id = %d",
					$this->user_id,
					$this->filtered_component,
					$this->filtered_item,
					$this->item_id,
					$this->bpaz_level,
					$this->last_updated,
					$this->id
				);
		} else {
			// We're dealing with a new record so insert
			$sql = $wpdb->prepare(
				"INSERT INTO {$bp->authz->table_name_acl_main} (
					user_id,
					filtered_component,
					filtered_item,
					item_id,
					bpaz_level,
					last_updated
				) VALUES (
					%d, %s, %s, %d, %d, %s
				)",
					$this->user_id,
					$this->filtered_component,
					$this->filtered_item,
					$this->item_id,
					$this->bpaz_level,
					$this->last_updated
				);
		}

		if ( !$result = $wpdb->query( $sql ) ) {
			//echo "Something bad happened";
			return false;
		}

		/* If inserting a new record, grab the last ID generated in the AUTO_INCREMENT
		 * field, the primary key field that uniquely identifies the newly created
		 * record in the Main ACL table. This will be needed if there are data to
		 * write to the Lists ACL table.
		 */
		if ( !$this->id ) {
			$this->id = $wpdb->insert_id;
		}

		// Need to decode JSON string before testing for empty.
		$this->old_lists_array = json_decode( $this->old_lists_array, true );

		// If processing a BPAz list, make sure that data exists in at least one of the lists arrays.
		if ( ( $this->bpaz_level == 3 || 4 ) && ( !empty( $this->old_lists_array ) || !empty( $this->group_user_list_id_array ) ) ) {

			// Set list type variables; used to target specific list subarray element
			if ( $this->bpaz_level == 3 ) {
				$list_type_old = 'grouplist';
				$list_type_new = 'group_list';
			} elseif ( $this->bpaz_level == 4 ) {
				$list_type_old = 'userlist';
				$list_type_new = 'user_list';
			}

			// Set the ACL Lists table child record foreign key field (id_main) to that of the current ACL Main id record
			$this->id_main = $this->id;

			/* Loop through the group_user_list_id_array array. This object array holds the newest
			 * user selections of groups or users from the ACL listbox. The array may hold two
			 * nested arrays, one for the group list and one for the user list. The contents of
			 * these subarrays will be compared to the contents of the old_lists_array object to
			 * determine what action needs to occur.
			 *
			 * See "Saving and Deleting ACL Lists Data" in the Developer's Guide section of the
			 * BuddyPress Privacy Manual for more details.
			 */
			if ( empty( $this->group_user_list_id_array[$list_type_new] ) && !empty( $this->old_lists_array[$list_type_old] ) ) {
				// Use all values in old array to delete corresponding list records
				$list_delete = $this->old_lists_array[$list_type_old];
				$list_insert = null;

			} elseif ( empty( $this->old_lists_array[$list_type_old]) && !empty( $this->group_user_list_id_array[$list_type_new] ) ) {
				// Use all values in new array to insert new list records
				$list_insert = $this->group_user_list_id_array[$list_type_new];
				$list_delete = null;

			} elseif ( !empty( $this->group_user_list_id_array[$list_type_new] ) && !empty( $this->old_lists_array[$list_type_old] ) ) {
				// Both arrays are populated; perform diff operations to ferret out unique elements.

				/* The result will hold all group or user ids that are not represented
				 * in the old_lists_array. The values in this array will be used to
				 * insert new list records.
				 */
				$list_insert = array_diff( $this->group_user_list_id_array[$list_type_new], $this->old_lists_array[$list_type_old] );

				/* The result will hold all group or user ids that are not represented
				 * in the new lists array. The values in this array will be used to
				 * delete new list records.
				 */
				$list_delete = array_diff( $this->old_lists_array[$list_type_old], $this->group_user_list_id_array[$list_type_new] );
			}

			/* Finally, we loop through the two arrays that contain the array difference
			 * results inserting new and then deleting old, deselected list records.
			 */

			// Insert new, unique list records
			if ( !empty( $list_insert ) ) {
				foreach ( $list_insert as $key => $value ) {

					$acl_list_record = new BP_Authz_ACL_Lists();

					$acl_list_record->id_main = $this->id_main;
					$acl_list_record->list_type = wp_filter_kses( $list_type_old );
					$acl_list_record->user_group_id = $value;

					if ( !$result = $acl_list_record->save() ) {
						//echo "Something bad happened";
						return false;
					}
				}
			}

			// Delete list records
			if ( !empty( $list_delete ) ) {
				foreach ( $list_delete as $key => $value ) {

					$deleted_list_success = BP_Authz_ACL_Lists::delete_by_id( $key );

					if ( $deleted_list_success == false ) {
						//echo "Something bad happened";
						return false;
					}
				}
			}
		}

		// Post-save action hook
		do_action( 'authz_acl_main_after_save', $this );

		return $result;
	}

	/** Delete a single record and any related child records
	 *
	 * If the InnoDB storage engine is being used, then the process
	 * of deleting is as simple as deleting the parent record. Any
	 * related records in the child table xx_bp_authz_acl_lists
	 * will automatically be deleted as well.
	 *
	 * When the default MyISAM storage engine is used, then
	 * a somewhat more complex method of deleting related child
	 * records is required.
	 */
	function delete( $id ) {
		global $wpdb, $bp;

		// InnoDB storage engine in use, cascade deletes
		if ( BP_AUTHZ_USE_INNODB == true ) {

			//***
			//echo "DELETING RECORD number =>{$id }<=:<br />InnoDB in use...<br />";

			$sql = $wpdb->prepare( "DELETE FROM {$bp->authz->table_name_acl_main} WHERE id = %d", $id );

			if ( !$wpdb->query($sql) ) {

				//***
				//echo "InnoDB: ERROR DELETING RECORD =>{$id }<= in DB<br />";

				// An error occured when attempting to delete parent record
				return false;
			}

			//***
			//echo "InnoDB: DELETION SUCCESS!<br />";

			return true;

		// Default MyISAM storage engine, need to first find and delete any/all related child records
		} else {

			//***
			//echo "DELETING RECORD =>{$id }<=:<br />MyISAM in use...<br />";

			$sql = $wpdb->prepare( "DELETE FROM {$bp->authz->table_name_acl_main} WHERE id = %d", $id );

			if ( !$wpdb->query($sql) ) {

				//***
				//echo "MyISAM: ERROR DELETING PARENT RECORD =>{$id }<= in DB<br />";

				// An error occured when attempting to delete parent record
				return false;
			}

			// Next, delete any related child records in xx_bp_authz_acl_lists.
			$child_deleted_success = BP_Authz_ACL_Lists::delete_children( $id );

			/* A return value of false may mean there was an error or that there
			 * were simply no records to delete. So, we will ignore the returned
			 * value, assuming that everything is okay. Isn't using InnoDB
			 * better! You can't beat cascade deletes.
			 */

			return true;
		}
	}

	// *** Not currently used; needs more work and testing; query and coding may not be correct
	// If a user account is deleted, purge records from table
	function delete_select_user_acl_records( $user_id ) {
		global $wpdb, $bp;

		// InnoDB storage engine in use, cascade deletes
		if ( BP_AUTHZ_USE_INNODB == true ) {

			$sql = $wpdb->prepare( "DELETE FROM {$bp->authz->table_name_acl_main} WHERE user_id = %d", $user_id );

			if ( !$wpdb->query($sql) ) {
				return false;
			}

			return true;

		// Default MyISAM storage engine, need to find and delete any/all related child records
		} else {

			// First delete any related child records in xx_bp_authz_acl_lists.
			//$child_deleted_success = BP_Authz_ACL_Lists::delete_children($this->id);

			//***
			/*Need the main record ID before deleting child ID
			 * Possibly loop through the main ACL table one record at a time
			 * grabbing record IDs and passing them to ACL Lists delete method?
			 */

			// First delete any child records
			BP_Authz_ACL_Lists::delete_children($this->id);

			//Next delete all parent records
			$sql = $wpdb->prepare( "DELETE FROM {$bp->authz->table_name_acl_main} WHERE user_id = %d", $user_id );

			if ( !$wpdb->query($sql) ) {
				return false;
			}

			return true;
		}
	}


	// *** Not currently used; needs more work and testing; query may not be correct
	/* If a user leaves a hidden group, then all list records for that user containing that group
	 * need to be purged from the xx_bp_table_name_acl_lists table. This is a special delete method
	 * that is called only when a user leaves a hidden group.
	 */
	function delete_hidden_group_from_user_listings( $group_id, $user_id ) {
		global $wpdb, $bp;

		$sql = $wpdb->prepare( "DELETE FROM {$bp->authz->table_name_acl_lists} lists INNER JOIN {$bp->authz->table_name_acl_main} main ON lists.id_main = main.id WHERE main.user_id = %d AND lists.user_group_id = %d", $user_id, $user_group_id );

		if ( !$wpdb->query($sql) ) {
			return false;
		}

		return true;
	}

	// Static Functions

	/* Queries offered:
	 *
	 * Get the entire ACL dataset (across all core components) for a given user
	 * Get the ACL recordset for a given component for a given user
	 * Get one ACL record for a given component for a given user (record id known)
	 * Get one ACL record for a given component for a given user (record id not known)
	 */

	// *** Not currently used; needs more work and testing; query may not be correct
	// Get the entire ACL dataset (across all core components) for a given user
	function get_user_acl_dataset( $user_id = null ) {
		global $wpdb, $bp;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		$acl = $wpdb->prepare("SELECT * FROM {$bp->authz->table_name_acl_main} AS p LEFT JOIN {$bp->authz->table_name_acl_lists} AS c ON p.id = c.id_main WHERE user_id = %d", $user_id );

		//$acl = $wpdb->prepare("SELECT * FROM {$bp->authz->table_name_acl_main} WHERE user_id = %d", $user_id );

		//*** Still need to build a unique ACL object array as done below in get_user_acl_privacy_item_by_id()

		if ( $privacy_dataset = $wpdb->get_results($acl, ARRAY_A) ) {
			return $privacy_dataset;
		} else {
			return false;
		}
	}

	// *** Not currently used; needs more work and testing; query may not be correct
	// Get the ACL recordset for a given component for a given user
	function get_user_acl_recordset_by_component( $user_id = null, $filtered_component = null ) {
		global $wpdb, $bp;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		$acl = $wpdb->prepare("SELECT * FROM {$bp->authz->table_name_acl_main} AS p LEFT JOIN {$bp->authz->table_name_acl_lists} AS c ON p.id = c.id_main WHERE user_id = %d AND filtered_component = %s", $user_id, $filtered_component );

		//$acl = $wpdb->prepare("SELECT * FROM {$bp->authz->table_name_acl_main} WHERE user_id = %d AND filtered_component = %s", $user_id, $filtered_component );

		//*** Still need to build a unique ACL object array as done below in get_user_acl_privacy_item_by_id()

		if ( $privacy_recordset = $wpdb->get_results($acl, ARRAY_A) ) {
			return $privacy_recordset;
		} else {
			return false;
		}
	}

	// *** Not currently used but works as expected
	// Get one ACL record for a given privacy item of a given component for a given user using record id
	function get_user_acl_privacy_item_by_id( $id ) {
		global $wpdb, $bp;

		$acl_main = $wpdb->prepare("SELECT p.id, p.user_id, p.filtered_component, p.filtered_item, p.item_id, p.bpaz_level, c.list_type, c.id AS list_id, c.user_group_id FROM {$bp->authz->table_name_acl_main} AS p LEFT JOIN {$bp->authz->table_name_acl_lists} AS c ON (p.id = c.id_main) WHERE p.id = %d ORDER BY c.list_type", $id );

		if ( $privacy_item = $wpdb->get_results($acl_main, ARRAY_A) ) {

			// build a unique ACL object array
			$acl_object = self::build_new_ACL_object( $privacy_item );

			//***
			/*
			echo '<br />ACL Object<br />';

			foreach ( $acl_object as $key => $value ) {
				echo "<pre>";
				echo "<strong>" . $key . ": </strong><br />";
				print_r( $value );
				echo "</pre><br />";
			}
			*/

			return $acl_object;
		} else {
			//echo 'Nothing to return!<br />';
			return false;
		}
	}

	// Get one ACL record for a given privacy item of a given component for a given user using parameters other than record id
	function get_user_acl_privacy_item_no_id( $user_id = null, $filtered_component, $filtered_item, $item_id ) {
		global $wpdb, $bp;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		/* As we don't have the ACL Main table record id, we cannot join to the ACL Lists table. The id field from the
		 * ACL Main table is the linked to id_main field of the ACL Lists table. Without the id, we cannot JOIN the two
		 * tables. So, we need to query in two steps.
		 *
		 */
		$acl_main = $wpdb->prepare("SELECT id, user_id, filtered_component, filtered_item, item_id, bpaz_level FROM {$bp->authz->table_name_acl_main} WHERE user_id = %d AND filtered_component = %s AND filtered_item = %s AND item_id = %d", $user_id, $filtered_component, $filtered_item, $item_id );

		// Now we grab any list records associated with Main ACL record.
		if ( $privacy_item = $wpdb->get_row($acl_main) ) {

			/* Populate an array with any ACL user or group list data
			 * from xx_bp_authz_acl_lists child table
			 */
			$acl_lists = self::get_user_group_lists($privacy_item->id);

			if ( $acl_lists ) {

				$count_list = count( $acl_lists );

				/* Finally, we iterate through the returned ACL Lists array and
				 * build our own unique object array that will be used in rendering
				 * the listboxes. The $rec_id variable stores the unique list record
				 * ID which is crucial information that is used when deleting a record
				 * from the ACL Lists table.
				 *
				 * See "Saving and Deleting ACL Lists Data" in the Developer's Guide
				 * section of the BuddyPress Privacy Manual for more details.
				 */
				for ( $i = 0; $i < $count_list; $i++ ) {

					// Iterate through ACL Lists nested array
					foreach ( $acl_lists[$i] as $key => $value ) {

						if ($value == 'grouplist' ) {

							$rec_id = $acl_lists[$i]['id'];

							$list_items['lists']['grouplist'][$rec_id] = $acl_lists[$i]['user_group_id'];

						} elseif ($value == 'userlist' ) {

							$rec_id = $acl_lists[$i]['id'];

							$list_items['lists']['userlist'][$rec_id] = $acl_lists[$i]['user_group_id'];
						}
					}

					//*** preserved old method for reference
					/*
					foreach ( $acl_lists[$i] as $key => $value ) {

						if ($value == 'grouplist' ) {

							$list_items['lists']['grouplist'][] = array( 'rec_id' => $acl_lists[$i]['id'], 'user_group_id' => $acl_lists[$i]['user_group_id'] );

						} elseif ($value == 'userlist' ) {

							$list_items['lists']['userlist'][] = array( 'rec_id' => $acl_lists[$i]['id'], 'user_group_id' => $acl_lists[$i]['user_group_id'] );
						}
					}
					*/
				}

				// Merge ACL array sets then convert back to object to be able to use object notation
				$result = (object)array_merge((array)$privacy_item, (array)$list_items);

				return $result;
			}

			return $privacy_item;
		} else {
			//echo 'Nothing to return!<br />';
			return false;
		}

	}

	private function get_user_group_lists($id_main) {
		return BP_Authz_ACL_Lists::get_user_group_lists_by_id($id_main);
	}

	/* This method is used to assemble a new ACL object, outputting
	 * an ACL Main table record and any ACL Lists table records in a
	 * format that is more usable by the application.
	 */
	private function build_new_ACL_object( $acl_array ) {

		$count_list = count( $acl_array );

		/* Iterate through the generated query-result set and build a new,
		 * unique ACL object array
		 */
		for ( $i = 0; $i < $count_list; $i++ ) {

			// Iterate through ACL nested array
			foreach ( $acl_array[$i] as $key => $value ) {

				if ($key == 'id' ) {

					$acl_object['id'] = $value;

				} elseif ($key == 'user_id' ) {

					$acl_object['user_id'] = $value;

				} elseif ($key == 'filtered_component' ) {

					$acl_object['filtered_component'] = $value;

				} elseif ($key == 'filtered_item' ) {

					$acl_object['filtered_item'] = $value;

				} elseif ($key == 'item_id' ) {

					$acl_object['item_id'] = $value;

				} elseif ($key == 'bpaz_level' ) {

					$acl_object['bpaz_level'] = $value;

				} elseif ($key == 'list_type' ) {

					if ($value == 'grouplist' ) {

						$rec_id = $acl_lists[$i]['id'];

						$list_items['lists']['grouplist'][$rec_id] = $acl_lists[$i]['user_group_id'];

					} elseif ($value == 'userlist' ) {

						$rec_id = $acl_lists[$i]['id'];

						$list_items['lists']['userlist'][$rec_id] = $acl_lists[$i]['user_group_id'];
					}

					//*** preserved old method for reference
					/*
					if ( $value == 'grouplist' ) {

						$acl_object['lists']['grouplist'][] = array( 'rec_id' => $acl_array[$i]['list_id'], 'user_group_id' => $acl_array[$i]['user_group_id'] );

					} elseif ( $value == 'userlist' ) {

						$acl_object['lists']['userlist'][] = array( 'rec_id' => $acl_array[$i]['list_id'], 'user_group_id' => $acl_array[$i]['user_group_id'] );
					}
					*/
				}
			}
		}

		return (object)$acl_object;
	}

}


/**
 * A child BPAz ACL table holding the specific user or group lists associated with
 * a particular filtered privacy item of a member's privacy object. Data is stored
 * in a basic normalized structure. If you are sharding your database, it is best
 * to keep both xx_bp_authz_acl_main and xx_bp_authz_acl_lists tables on the same
 * partition.
 *
 * @package BP-Privacy Classes
 * @author Jeff Sayre
 * @see BP_Authz_ACL_Main
 */
//class BP_Authz_ACL_Lists extends BP_Authz_ACL_Main {
class BP_Authz_ACL_Lists {

	// Field declarations for xx_bp_authz_acl_lists table
	public $id;
	public $id_main;
	public $list_type;
	public $user_group_id;

	/*
	$acl_list_record->id = $id_list;
	$acl_list_record->id_main = $id_main;
	$acl_list_record->list_type = wp_filter_kses( $list_type );
	$acl_list_record->user_group_id = $value;
	*/

	public function __construct( $id_main = null, $list_type = null ) {

		if ( $id_main ) {
			$this->id_main = $id_main;
			$this->list_type = $list_type;
			$this->populate( $this->id_main, $this->list_type );
		}
	}

	private function populate( $id_main, $list_type ) {
		global $wpdb, $bp;

		if ( $acl_lists = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->authz->table_name_acl_lists} WHERE id_main = %d", $this->id_main ) ) ) {
			$this->id = $acl_lists->id;
			$this->id_main = $acl_lists->id_main;
			$this->list_type = stripslashes($acl_lists->list_type);
			$this->user_group_id = $acl_lists->user_group_id;
		}
	}

	/* Saves any user or group listings associated with a given ACL record for a given user.
	 * This only occurs when the selected ACL level is 3 ("Members of These Groups")
	 * or 4 ("These Users Only").
	 */
	function save() {
		global $wpdb, $bp;

		// Pre-save filter hooks
		$this->id_main = apply_filters( 'authz_acl_id_main_before_save', $this->id_main, $this->id );
		$this->list_type = apply_filters( 'authz_acl_list_type_before_save', $this->list_type, $this->id );
		$this->user_group_id = apply_filters( 'authz_acl_user_group_id_before_save', $this->user_group_id, $this->id );

		// Pre-save action hook
		do_action( 'authz_acl_lists_before_save', $this );

		if ( $this->id ) {
			// We're dealing with an existing record so update
			$sql = $wpdb->prepare(
				"UPDATE {$bp->authz->table_name_acl_lists} SET
					id_main = %d,
					list_type = %s,
					user_group_id = %d
				WHERE id = %d",
					$this->id_main,
					$this->list_type,
					$this->user_group_id,
					$this->id
				);
		} else {
			// We're dealing with a new record so insert
			$sql = $wpdb->prepare(
				"INSERT INTO {$bp->authz->table_name_acl_lists} (
					id_main,
					list_type,
					user_group_id
				) VALUES (
					%d, %s, %d
				)",
					$this->id_main,
					$this->list_type,
					$this->user_group_id
				);
		}

		if ( !$result = $wpdb->query( $sql ) ) {
			//echo "Something bad happened";
			return false;
		}

		// Not currently used
		if ( !$this->id ) {
			$this->id = $wpdb->insert_id;
		}

		// Post-save action hook
		do_action( 'authz_acl_lists_after_save', $this );

		return $result;
	}

	/* The primary, default delete method for ACL Lists. If a user has a previously
	 * saved user and/or group list associated with a given ACL recordset but changes
	 * the BPAz level to something other than 3 or 4, the data is retained for future
	 * reference. But, if a user sets the BPAz level for an existing
	 * list to 0 ("All Users"), then all list records associated with the parent record
	 * will be purged as the parent record is deleted as well.
	 */
	function delete( $id_main ) {
		global $wpdb, $bp;

		$sql = $wpdb->prepare( "DELETE FROM {$bp->authz->table_name_acl_lists} WHERE id_main = %d", $id_main );

		if ( !$wpdb->query($sql) ) {
			return false;
		}

		return true;
	}

	/* This method is called only when the default MyISAM storage engine
	 * is in use. Although it is identical in function to above method,
	 * it is seperated out so as to maintain a logical distinction between
	 * the two operations.
	 *
	 * First delete any and all child records before deleting the related
	 * parent record. Child records should always be deleted before any
	 * associated parent record.
	 */
	function delete_children( $id_main ) {
		global $wpdb, $bp;

		$sql = $wpdb->prepare( "DELETE FROM {$bp->authz->table_name_acl_lists} WHERE id_main = %d", $id_main );

		if ( !$wpdb->query($sql) ) {

			/*** May need to pass something other than false if there are no child records
			 * for given passed $id_main. False does not differentiate between error
			 * and simply no records existing.
			 */

			//***
			//echo '<br />Something happened with child record deletion...<br />';

			return false;
		}

		return true;
	}

	/* When a given record element reference is deselected in a listbox by a user, it
	 * must be individually deleted.
	 */
	function delete_by_id( $id ) {
		global $wpdb, $bp;

		$sql = $wpdb->prepare( "DELETE FROM {$bp->authz->table_name_acl_lists} WHERE id = %d", $id );

		if ( !$wpdb->query($sql) ) {
			return false;
		}

		return true;
	}

	/* If a WP user account or BP group is deleted, purge records containing
	 * that $user_id or $group_id from other users' ACL recordsets within
	 * this child table -- xx_bp_table_name_acl_lists. This is a special
	 * delete method as it is not tiggered by a deletion action in the
	 * main ACL table. It occurs exclusively in the child table.
	 */
	function delete_select_user_group_listings( $user_group_id ) {
		global $wpdb, $bp;

		$sql = $wpdb->prepare( "DELETE FROM {$bp->authz->table_name_acl_lists} WHERE user_group_id = %d", $user_group_id );

		if ( !$wpdb->query($sql) ) {
			return false;
		}

		return true;
	}

	// Get all ACL Lists records for a given privacy item of a given component for a given user using Main ACL id
	function get_user_group_lists_by_id( $id_main ) {
		global $wpdb, $bp;

		if ( !$id_main )
			return false;

		$acl_lists = $wpdb->prepare("SELECT list_type, id, user_group_id FROM {$bp->authz->table_name_acl_lists} WHERE id_main = %d ORDER BY list_type", $id_main );

		if ( $privacy_item = $wpdb->get_results($acl_lists, ARRAY_A) ) {
			return $privacy_item;
		} else {
			return false;
		}
	}

}

?>