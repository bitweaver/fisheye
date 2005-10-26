<?php
/**
 * Thumbnailer
 *
 * usage is simple:
 *		php -q thumbnailer.php [# of thumbnails]
 * example:
 *		php -q thumbnailer.php 20
 * suggested crontab entry runs the thumbnailer every minute:
 *		* * * * * apache php -q /path/to/bitweaver/fisheye/thumbnailer.php 20 >> /var/log/httpd/thumbnail_log
 *
 * @version $Header: /cvsroot/bitweaver/_bit_fisheye/thumbnailer.php,v 1.2.2.7 2005/10/26 17:49:10 spiderr Exp $
 * @package fisheye
 * @subpackage functions
 */

	global $gBitSystem, $_SERVER;

	$_SERVER['SCRIPT_URL'] = '';
	$_SERVER['HTTP_HOST'] = '';
	$_SERVER['HTTP_HOST'] = '';
	$_SERVER['HTTP_HOST'] = '';
	$_SERVER['SERVER_NAME'] = '';

/**
 * required setup
 */
	if( !empty( $argc ) ) {
		// reduce feedback for command line to keep log noise way down
		define( 'BIT_PHP_ERROR_REPORTING', E_ERROR | E_PARSE );
	}

	// running from cron can cause us not to be in the right dir.
	chdir( dirname( __FILE__ ) );
	require_once( '../bit_setup_inc.php' );
	require_once( FISHEYE_PKG_PATH.'FisheyeImage.php' );

	// add some protection for arbitrary thumbail execution.
	// if argc is present, we will trust it was exec'ed command line.
	if( empty( $argc ) && !$gBitUser->isAdmin() ) {
		$gBitSystem->fatalError( 'You cannot run the thumbnailer' );
	}

	$thumbCount = ( !empty( $argv[1] ) ) ? $argv[1] : ( !empty( $_REQUEST['thumbnails'] ) ? $_REQUEST['thumbnails'] : 10);

	$gBitSystem->mDb->StartTrans();

	$sql = "SELECT tq.content_id AS hash_key, tq.*
			FROM `".BIT_DB_PREFIX."tiki_thumbnail_queue` tq
			WHERE tq.begin_date IS NULL
			ORDER BY tq.queue_date";
	$rs = $gBitSystem->mDb->query( $sql, NULL, $thumbCount );

	$processContent = array();
	while( !$rs->EOF ) {
		$processContent[$rs->fields['content_id']] = $rs->fields;
		$sql2 = "UPDATE `".BIT_DB_PREFIX."tiki_thumbnail_queue` SET `begin_date`=? WHERE `content_id`=?";
		$rs2 = $gBitSystem->mDb->query( $sql2, array( date( 'U' ), $rs->fields['content_id'] ) );
		$rs->MoveNext();
	}

	$gBitSystem->mDb->CompleteTrans();

	$log = array();
	$total = date( 'U' );
	foreach( array_keys( $processContent ) as $contentId ) {
		$image = new FisheyeImage( NULL, $contentId );
		$begin = date( 'U' );
		if( $processContent[$contentId]['resize_original'] ) {
			$image->resizeOriginal( $processContent[$contentId]['resize_original'] );
		}
		if( $image->renderThumbnails() ) {
			$log[$contentId]['message'] = 'SUCCESS: Thumbnails created';
			$sql3 = "UPDATE `".BIT_DB_PREFIX."tiki_thumbnail_queue` SET `begin_date`=?, `end_date`=? WHERE `content_id`=?";
			$rs3 = $gBitSystem->mDb->query( $sql3, array( $begin, $gBitSystem->getUTCTime(), $contentId ) );
		} else {
			$log[$contentId]['message'] = ' ERROR: '.$image->mErrors['thumbnail'];
		}
		$log[$contentId]['time'] = date( 'd/M/Y:H:i:s O' );
		$log[$contentId]['duration'] = date( 'U' ) - $begin;
		$log[$contentId]['delay'] = date( 'U' ) - $total;
	}

	foreach( array_keys( $log ) as $contentId ) {
		// generate something that kinda looks like apache common log format
		print $contentId.' - - ['.$log[$contentId]['time'].'] "'.$log[$contentId]['message'].'" '.$log[$contentId]['duration']."seconds <br/>\n";
	}

	if( count($processContent) ) {
		print '# '.count($processContent)." images processed in ".(date( 'U' ) - $total)." seconds<br/>\n";
	}

?>
