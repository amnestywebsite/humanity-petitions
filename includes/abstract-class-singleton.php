<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use ReflectionProperty;

/**
 * Singleton helper class
 */
abstract class Singleton {

	/**
	 * Instance variable
	 *
	 * @var static
	 */
	protected static $instance = null;

	/**
	 * Retrieve class instance
	 *
	 * @return static
	 */
	final public static function instance() {
		if ( ! static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Property getter
	 *
	 * @param string $property the property name
	 *
	 * @return mixed
	 */
	final public function __get( $property ) {
		if ( ! property_exists( static::class, $property ) ) {
			return null;
		}

		$prop = new ReflectionProperty( static::class, $property );

		if ( $prop->isStatic() ) {
			return $prop->getValue();
		}

		return $prop->getValue( static::instance() );
	}

	/**
	 * Inaccessible method helper
	 *
	 * @param string $method    the method being called
	 * @param array  $arguments parameters passed to method
	 *
	 * @throws \RuntimeException thrown if method does not exist
	 *
	 * @return mixed
	 */
	final public function __call( $method, $arguments ) {
		$self = static::instance();

		if ( ! method_exists( $self, $method ) ) {
			throw new \RuntimeException( esc_html( sprintf( 'Method %s does not exist on %s', $method, static::class ) ) );
		}

		return call_user_func_array( [ $self, $method ], $arguments );
	}

	/**
	 * Inaccessible static method helper
	 *
	 * @param string $method    the method being called
	 * @param array  $arguments parameters passed to method
	 *
	 * @throws \RuntimeException thrown if method does not exist
	 *
	 * @return mixed
	 */
	final public static function __callStatic( $method, $arguments ) {
		$self = static::instance();

		if ( ! method_exists( $self, $method ) ) {
			throw new \RuntimeException( esc_html( sprintf( 'Method %s does not exist on %s', $method, static::class ) ) );
		}

		return call_user_func_array( [ $self, $method ], $arguments );
	}

	/**
	 * Prevent class being clondd
	 *
	 * @return void
	 */
	final protected function __clone() {
	}

}
