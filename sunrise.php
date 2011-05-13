<?php
//
// Cache modifications by Jonas Bjork, jonas.bjork@aller.se
//
// let the site admin page catch the VHOST == 'no'
if( VHOST == 'yes' ) {
$dm_domain = $wpdb->escape( preg_replace( "/^www\./", "", $_SERVER[ 'HTTP_HOST' ] ) );
$file = "/tmp/domain-mapping-".sha1($dm_domain).".cache";
$filemtime = @filemtime( $file );

// 14d = 1209600s
if (!$filemtime or (time() - $filemtime >= 1209600)) {
  $wpdb->dmtable = $wpdb->base_prefix . 'domain_mapping';
  $wpdb->suppress_errors();
  $domain_mapping_id = $wpdb->get_var( "SELECT blog_id FROM {$wpdb->dmtable} WHERE domain = '{$dm_domain}' LIMIT 1" );
  $wpdb->suppress_errors( false );
  if( $domain_mapping_id ) {
    $cb = $wpdb->get_row("SELECT * FROM {$wpdb->blogs} WHERE blog_id = '$domain_mapping_id' LIMIT 1");
    $cs = $wpdb->get_row( "SELECT * from {$wpdb->site} WHERE id = '{$cb->site_id}' LIMIT 0,1" );

$data = array(
      'dm_domain' => $dm_domain,
      'wp_dmtable' => $wpdb->dmtable,
      'current_blog' => $cb,
      'current_site' => $cs,
      'current_domain' => $_SERVER['HTTP_HOST'],
      'current_path' => '/',
      'blog_id' => $domain_mapping_id,
      'site_id' => $cb->site_id );
    file_put_contents($file, serialize($data));

  }
} else {
  $fh = fopen($file, 'r');
  $data = fread($fh, filesize($file));
  fclose($fh);
  $data = unserialize($data);
}
$dm_domain = $data['dm_domain'];
$wpdb->dmtable = $data['wp_dmtable'];
$current_blog = $data['current_blog'];
$current_blog->domain = $data['current_domain'];
$current_blog->path = $data['current_path'];
$blog_id = $data['blog_id'];
$site_id = $data['site_id'];
define( 'COOKIE_DOMAIN', $data['current_domain']);
$current_site = $data['current_site'];
define('DOMAIN_MAPPING', 1);

} // if VHOST
?>
