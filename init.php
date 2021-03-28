<?php
/*
Plugin Name: Bookme Pricing Table
Plugin URI: https://inallerust.nl
Description: Easily add a pricing table based on the services you added with the Bookme plugin by 'Bylancer'
Version: 4.0
Author: Jorrin
Author URI: https://inallerust.nl
Text Domain: bookme-pricing-table
*/

function init( $attr ) {
	$bmpt = new BookmePricingTable();
	
	return $bmpt->bookme_pricing_table( $attr );
}

add_shortcode( 'bookme_pricing_table', 'init' );

class BookmePricingTable {
	/**
	 * @param $attr
	 *
	 * @return string
	 */
	public function bookme_pricing_table( $attr ): string {
		global $wpdb;
		
		$content = '';
		
		$category_id = $attr['category'];
		$style       = $attr['style'] ?? 'list';
		
		$table_category = $wpdb->prefix . 'bm_categories';
		$table_service  = $wpdb->prefix . 'bm_services';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE 'bm_categories'" ) != 'bm_categories' ) {
			if ( ! empty( $category_id ) ) {
				$sql = "SELECT * FROM $table_category WHERE id='$category_id'";
			} else {
				$sql = "SELECT * FROM $table_category";
			}
			$categories = $wpdb->get_results( $sql );
			$sql        = "SELECT * FROM $table_service";
			$services   = $wpdb->get_results( $sql );
			
			switch ( $style ) {
				case 'table':
					$content = $this->renderTable( $categories, $services );
					break;
				case 'list':
				default:
					$content = $this->renderList( $categories, $services );
			}
			
		} else {
			error_log( "Database table doesn't exist" );
		}
		
		return $content;
	}
	
	/**
	 * @param $categories
	 * @param $services
	 *
	 * @return string
	 */
	public function renderTable( $categories, $services ): string {
		$content = '';
		setlocale( LC_MONETARY, 'nl_NL.UTF-8' );
		$content .= '<table>';
		foreach ( $categories as $category ) {
			$content .= '<thead>';
			$content .= '<tr>' . '<td>' . $category->name . '</td>' . '<td>' . 'Prijs' . '</td>' . '</tr>';
			$content .= '</thead>';
			
			$content .= '<tbody>';
			foreach ( $services as $service ) {
				if ( $service->catId === $category->id ) {
					$content .= '<tr>' . '<td>' . $service->name . '</td>' . '<td>' . money_format( '%(#1n', $service->price ) . '</td>' . '</tr>';
				}
			}
			$content .= '</tbody>';
		}
		$content .= '<table>';
		
		return $content;
	}
	
	/**
	 * @param $categories
	 * @param $services
	 *
	 * @return string
	 */
	public function renderList( $categories, $services ): string {
		$content = '';
		setlocale( LC_MONETARY, 'nl_NL.UTF-8' );
		
		foreach ( $categories as $category ) {
			$content .= '<div class="bmpt">';
			$content .= '<div class="bmpt-head"><h3>' . $category->name . '</h3></div>';
			$content .= '<div class="bmpt-holder">';
			foreach ( $services as $service ) {
				if ( $service->category_id === $category->id ) {
					$content .= '
                <div class="bmpt-item">
                    <div class="bmpt-item-inner">
                        <h5 class="bmpt-title-price-holder clearfix">
                            <div class="bmpt-title">' . $service->title . '<span class="bmpt-duration"> (' . \Bookme\Inc\Mains\Functions\DateTime::seconds_to_interval( $service->duration ) . ')</span></div>
                            <div class="bmpt-border"></div>
                            <div class="bmpt-price-holder">' . str_replace( ' ', '', money_format( '%(#1n', $service->price ) ) . '</div>
                        </h5> ';
					if ( strlen( $service->info ) > 0 ) {
						$content .= '<div class="bmpt-desc">' . $service->info . '</div>';
					}
					$content .= '</div>';
					$content .= '</div>';
				}
			}
			$content .= '</div>';
			$content .= '</div>';
		}
		
		$content .= '
<style>

.bmpt {
    border: 1px solid #D6B981;
    background-color: #FFF;
    padding: 1.5rem 2rem;
    margin-bottom: 20px;
    height: 100%;
}

@media screen and (min-width: 768px) {
    .bmpt {
        padding: 1rem;
    }
}

.bmpt-head {
    text-align: center;
    margin-bottom: 2rem;
}

.bmpt-head h3 {
    margin: 0 !important;
}

.bmpt-item {
    position: relative;
    display: table;
    table-layout: fixed;
    height: 100%;
    width: 100%;
}

.bmpt-item:not(:last-child) {
    margin-bottom: 2rem;
}

.bmpt-item-inner {
    position: relative;
    display: table-cell;
    height: 100%;
    width: 100%;
    vertical-align: top;
    text-align: left;
}

.bmpt-title-price-holder {
    position: relative;
    display: flex;
    align-items: end;
    margin: 0 !important;
}

.bmpt-title {
    margin: 0 0 .25rem;
    text-transform: uppercase;
    word-break: break-all;
}

.bmpt-border {
    border-bottom: 1px dotted #000;
    flex-grow: 1;
    padding-top: 1rem;
    margin: 0 .25rem;
    min-width: 1rem;
}

.bmpt-price-holder {
    text-align: right;
    word-break: normal;
}

.bmpt-duration {
	word-break: normal;
	white-space: nowrap;
}
</style>';
		
		return $content;
	}
	
	public static function seconds_to_interval( $duration ): string {
		$duration = (int) $duration;
		
		$weeks   = (int) ( $duration / WEEK_IN_SECONDS );
		$days    = (int) ( ( $duration % WEEK_IN_SECONDS ) / DAY_IN_SECONDS );
		$hours   = (int) ( ( $duration % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
		$minutes = (int) ( ( $duration % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
		
		$parts = [];
		
		if ( $weeks > 0 ) {
			$parts[] = sprintf( _n( '%d week', '%d weeks', $weeks, 'bookme_pricing_table' ), $weeks );
		}
		if ( $days > 0 ) {
			$parts[] = sprintf( _n( '%d day', '%d days', $days, 'bookme_pricing_table' ), $days );
		}
		if ( $hours > 0 ) {
			$parts[] = sprintf( __( '%d h', 'bookme_pricing_table' ), $hours );
		}
		if ( $minutes > 0 ) {
			$parts[] = sprintf( __( '%d min', 'bookme_pricing_table' ), $minutes );
		}
		
		return implode( ' ', $parts );
	}
}