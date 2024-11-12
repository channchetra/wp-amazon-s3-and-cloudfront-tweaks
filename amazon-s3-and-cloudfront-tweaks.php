<?php
/*
Plugin Name: WP Offload Media Tweaks
Plugin URI: http://github.com/deliciousbrains/wp-amazon-s3-and-cloudfront-tweaks
Description: WP Offload Media Configuration Tweaks for Sihanouk Website
Author: Chetra Chann
Version: 0.6.0
Author URI: http://deliciousbrains.com
Network: True
*/

// Copyright (c) 2015 Delicious Brains. All rights reserved.
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;

class Amazon_S3_and_CloudFront_Tweaks {

	/**
	 * The constructor holds the `add_filter` and `add_action` statements that can be uncommented to activate them.
	 *
	 * Please only uncomment the statements you need after making sure their respective functions are correctly
	 * updated for your needs.
	 */
	public function __construct() {
		/*
		 * Custom S3 API Example: MinIO
		 * @see https://min.io/
		 */
		add_filter( 'as3cf_aws_s3_client_args', array( $this, 'minio_s3_client_args' ) );
		add_filter( 'as3cf_aws_get_regions', array( $this, 'minio_get_regions' ) );
		add_filter( 'as3cf_aws_s3_url_domain', array( $this, 'minio_s3_url_domain' ), 10, 5 );
		//add_filter( 'as3cf_upload_acl', array( $this, 'minio_upload_acl' ), 10, 1 );
		//add_filter( 'as3cf_upload_acl_sizes', array( $this, 'minio_upload_acl' ), 10, 1 );
		// add_filter( 'as3cf_aws_s3_console_url', array( $this, 'minio_s3_console_url' ) );
		//add_filter( 'as3cf_aws_s3_console_url_prefix_param', array( $this, 'minio_s3_console_url_prefix_param' ) );
	}
	/*
	 * >>> MinIO Examples Start
	 */

	/**
	 * This filter allows you to adjust the arguments passed to the provider's service specific SDK client.
	 *
	 * The service specific SDK client is created from the initial provider SDK client, and inherits most of its config.
	 * The service specific SDK client is re-created more often than the provider SDK client for specific scenarios, so if possible
	 * set overrides in the provider client rather than service client for a slight improvement in performance.
	 *
	 * @see     https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html#___construct
	 * @see     https://docs.min.io/docs/how-to-use-aws-sdk-for-php-with-minio-server.html
	 *
	 * @handles `as3cf_aws_s3_client_args`
	 *
	 * @param array $args
	 *
	 * @return array
	 *
	 * Note: A good place for changing 'signature_version', 'use_path_style_endpoint' etc. for specific bucket/object actions.
	 */
	public function minio_s3_client_args( $args ) {
		// Example changes endpoint to connect to a local MinIO server configured to use port 54321 (the default MinIO port is 9000).
		$args['endpoint'] = 'http://10.100.5.200:9000';

		// Example forces SDK to use endpoint URLs with bucket name in path rather than domain name as required by MinIO.
		$args['use_path_style_endpoint'] = true;

		return $args;
	}

	/**
	 * This filter allows you to add or remove regions for the provider.
	 *
	 * @handles `as3cf_aws_get_regions`
	 *
	 * @param array $regions
	 *
	 * @return array
	 *
	 * MinIO regions, like Immortals in Highlander, there can be only one.
	 */
	public function minio_get_regions( $regions ) {
		$regions = array(
			'ap-southeast-1' => 'Default',
		);

		return $regions;
	}

	/**
	 * This filter allows you to change the URL used for serving the files.
	 *
	 * @handles `as3cf_aws_s3_url_domain`
	 *
	 * @param string $domain
	 * @param string $bucket
	 * @param string $region
	 * @param int    $expires
	 * @param array  $args Allows you to specify custom URL settings
	 *
	 * @return string
	 */
	public function minio_s3_url_domain( $domain, $bucket, $region, $expires, $args ) {
		// MinIO doesn't need a region prefix, and always puts the bucket in the path.
		return 'asset.cambodia.gov.kh/' . $bucket;
	}

	/**
	 * Normally these filters allow you to change the default Access Control List (ACL)
	 * permission for an original file and its thumbnails when offloaded to bucket.
	 * However, MinIO doesn't do ACLs and defaults to private. So while this filter handler
	 * doesn't change anything in the bucket, it does tell WP Offload Media it needs sign URLs.
	 * In this handler we're just accepting the ACL and not bothering with any other params
	 * from the two filters.
	 *
	 * @handles `as3cf_upload_acl`
	 * @handles `as3cf_upload_acl_sizes`
	 *
	 * @param string $acl defaults to 'public-read'
	 *
	 * @return string
	 *
	 * Note: Only enable this if you are happy with signed URLs and haven't changed the bucket's policy to "Read Only" or similar.
	 */
	public function minio_upload_acl( $acl ) {
		return 'private';
	}

	/**
	 * This filter allows you to change the base URL used to take you to the provider's console from WP Offload Media's settings.
	 *
	 * @handles `as3cf_aws_s3_console_url`
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function minio_s3_console_url( $url ) {
		return 'http://mptc.gov.kh/';
	}

	/**
	 * The "prefix param" denotes what should be in the console URL before the path prefix value.
	 *
	 * For example, the default for AWS/S3 is "?prefix=".
	 *
	 * The prefix is usually added to the console URL just after the bucket name.
	 *
	 * @handles `as3cf_aws_s3_console_url_prefix_param`
	 *
	 * @param $param
	 *
	 * @return string
	 *
	 * MinIO just appends the path prefix directly after the bucket name.
	 */
	public function minio_s3_console_url_prefix_param( $param ) {
		return '/';
	}
}

new Amazon_S3_and_CloudFront_Tweaks();
