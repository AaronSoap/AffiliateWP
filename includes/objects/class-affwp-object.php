<?php
/**
 * Objects: Base Object
 *
 * @package AffiliateWP
 * @category Core
 *
 * @since 1.9
 */

namespace AffWP;

/**
 * Implements a base object to be extended by core objects.
 *
 * @since 1.9
 * @abstract
 */
abstract class Object {

	/**
	 * Whether the object members have been filled.
	 *
	 * @since 1.9
	 * @access protected
	 * @var bool|null
	 */
	protected $filled = null;

	/**
	 * Object group.
	 *
	 * Should be initialized in extending class versions of get_instance().
	 *
	 * @since 1.9
	 * @access public
	 * @static
	 * @var string
	 */
	public static $object_group;

	/**
	 * Retrieves the object instance.
	 *
	 * @since 1.9
	 * @access public
	 * @static
	 *
	 * @param int $object Object ID.
	 * @return object|false Object instance or false.
	 */
	public static function get_instance( $object_id ) {
		if ( ! (int) $object_id ) {
			return false;
		}

		$Sub_Class    = get_called_class();
		$cache_key    = self::get_cache_key( $object_id );
		$cache_group  = static::$object_type;
		$object_group = static::$object_group;

		$_object = wp_cache_get( $cache_key, $cache_group );

		if ( false === $_object ) {
			$_object = affiliate_wp()->{$object_group}->get( $object_id );

			if ( ! $_object ) {
				return false;
			}

			$_object = self::fill_vars( $_object );

			wp_cache_add( $cache_key, $_object, $cache_group );
		} elseif ( empty( $_object->filled ) ) {
			$_object = self::fill_vars( $_object );
		}
		return new $Sub_Class( $_object );
	}

	/**
	 * Retrieves the built cache key for the given single object.
	 *
	 * @since 1.9
	 * @access public
	 * @static
	 *
	 * @see Object::get_instance()
	 * @see affwp_clean_item_cache()
	 *
	 * @param int $object_id Object ID.
	 * @return string Cache key for the object type and ID.
	 */
	public static function get_cache_key( $object_id ) {
		return md5( static::$cache_token . ':' . $object_id );
	}

	/**
	 * Object constructor.
	 *
	 * @since 1.9
	 * @access public
	 * @param mixed $object Object to populate members for.
	 */
	public function __construct( $object ) {
		foreach ( get_object_vars( $object ) as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Retrieves the value of a given property.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param string $key Property to retrieve a value for.
	 * @return mixed Otherwise, the value of the property if set.
	 */
	public function __get( $key ) {
		if ( 'ID' === $key ) {
			$object_group = static::$object_group;
			$primary_key  = affiliate_wp()->{$object_group}->primary_key;

			return $this->{$primary_key};
		}

		if ( isset( $this->{$key} ) ) {
			return $this->{$key};
		}
	}

	/**
	 * Sets a property.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @see set()
	 *
	 * @param string $key   Property name.
	 * @param mixed  $value Property value.
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * Sets an object property value and optionally save.
	 *
	 * @internal Note: Checking isset() on $this->{$key} is missing here because
	 *           this method is also used directly by __set() which is leveraged for
	 *           magic properties.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param string $key   Property name.
	 * @param mixed  $value Property value.
	 * @param bool   $save  Optional. Whether to save the new value in the database.
	 * @return int|false True if the value was set. If `$save` is true, true if the save was successful.
	 *                   False if `$save` is true and the save was unsuccessful. false otherwise.
	 */
	public function set( $key, $value, $save = false ) {
		$this->$key = static::sanitize_field( $key, $value );

		if ( true === $save ) {
			// Only real properties can be saved.
			$keys = array_keys( get_class_vars( get_called_class() ) );

			if ( ! in_array( $key, $keys ) ) {
				return false;
			}

			return $this->save();
		}

		return true;
	}

	/**
	 * Saves an object with current property values.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		// Refresh the instance before save.
		static::get_instance( $this->ID );

		$Sub_Class    = get_called_class();
		$object_type  = $Sub_Class::$object_type;
		$object_group = $Sub_Class::$object_group;

		switch ( $object_type ) {
			case 'referral':
				$updated = affiliate_wp()->referrals->update_referral( $this->ID, $this->to_array() );
				break;

			case 'visit':
				$updated = affiliate_wp()->visits->update_visit( $this->ID, $this->to_array() );
				break;

			default:
				$updated = affiliate_wp()->{$object_group}->update( $this->ID, $this->to_array(), '', $object_type );
				break;
		}

		if ( $updated ) {
			return true;
		}

		return false;
	}

	/**
	 * Converts the given object to an array.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param mixed $object Object.
	 * @return array Array version of the given object.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Fills object members.
	 *
	 * @since 1.9
	 * @access public
	 * @static
	 *
	 * @param object|array $object Object or array of object data.
	 * @return object|array Object or data array with filled members.
	 */
	public static function fill_vars( $object ) {
		if ( is_object( $object ) ) {
			if ( isset( $object->filled ) ) {
				return $object;
			}

			foreach ( array_keys( get_object_vars( $object ) ) as $field ) {
				$object->$field = static::sanitize_field( $field, $object->$field );
				$object->filled = true;
			}
		} elseif ( is_array( $object ) ) {
			if ( isset( $object['filled'] ) ) {
				return $object;
			}

			foreach ( array_keys( $object ) as $field ) {
				$object[ $field ] = static::sanitize_field( $field, $object[ $field ] );
				$object['filled'] = true;
			}
		}
		return $object;
	}

	/**
	 * Sanitizes a given object field's value.
	 *
	 * Sub-class should override this method.
	 *
	 * @since 1.9
	 * @access public
	 * @static
	 *
	 * @param string $field Object field.
	 * @param mixed  $value Object field value.
	 * @return mixed Sanitized value for the given field.
	 */
	public static function sanitize_field( $field, $value ) {
		return $value;
	}

}