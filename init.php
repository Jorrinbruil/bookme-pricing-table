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
			if ( !empty($category_id) ) {
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
			$content .= '<div class="bm-pricing-table">';
			$content .= '<div class="bm-pricing-table-head"><h3>' . $category->name . '</h3></div>';
			$content .= '<div class="bm-pricing-table-holder">';
			foreach ( $services as $service ) {
				if ( $service->category_id === $category->id ) {
					$content .= '
                <div class="bm-pricing-table-item">
                    <div class="bm-pricing-table-item-inner">
                        <div class="bm-pricing-table-title-price-holder clearfix">
                            <h5 class="bm-pricing-table-title">
                                <span class="bm-pricing-table-title-area">' . $service->title . '</span>
                            </h5>
                            <div class="bm-pricing-table-price-holder">
                                <h5 class="bm-pricing-table-price">' . str_replace( ' ', '', money_format( '%(#1n', $service->price ) ) . '</h5>
                            </div>
                        </div> ';
					if ( strlen( $service->info ) > 0 ) {
						$content .= '<p class="bm-pricing-table-desc">' . $service->info . '</p>';
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

.bm-pricing-table {
    border: 1px solid #D6B981;
    background-color: #FFF;
    padding: 1.5rem 2rem 1.5rem;
    margin-bottom: 20px;
    height: 100%;
}

@media screen and (min-width: 768px) {
    .bm-pricing-table {
        padding: 2.5rem 3rem 2.5rem;
    }
}

.bm-pricing-table-head {
    text-align: center;
    margin-bottom: 2.5rem;
}

.bm-pricing-table-head h3 {
    margin: 0 !important;
}

.bm-pricing-table-item {
    position: relative;
    display: table;
    table-layout: fixed;
    height: 100%;
    width: 100%;
}

.bm-pricing-table-item:not(:last-child) {
    margin-bottom: 2.5rem;
}

.bm-pricing-table-item-inner {
    position: relative;
    display: table-cell;
    height: 100%;
    width: 100%;
    vertical-align: top;
    text-align: left;
}

.clearfix:after, .clearfix:before {
    content: " ";
    display: table;
}

.clearfix:after {
    clear: both;
}

.bm-pricing-table-title-price-holder {
    position: relative;
}

.bm-pricing-table-title {
    margin: 0 0 4px;
    width: 87%;
    float: left;
    position: relative;
    box-sizing: border-box;
    text-transform: uppercase;
}

.bm-pricing-table-title:after {
    position: relative;
    content: \'\';
    bottom: 4px;
    width: 100%;
    height: 1px;
    border-bottom: 1px dotted #000;
    display: table-cell;
}

.bm-pricing-table-title-area {
    position: relative;
    top: 1px;
    padding-right: 15px;
    display: table-cell;
    white-space: nowrap;
}

.bm-pricing-table-price-holder {
    float: right;
    width: 10%;
    text-align: right;
}

.bm-pricing-table-price {
    margin: 0;
    letter-spacing: 0;
}
</style>';
		
		return $content;
	}
}