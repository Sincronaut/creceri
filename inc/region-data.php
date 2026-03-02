<?php
/** -------- route helpers -------- */

function child_route_parts(): array {
  $parts = [];
  if ($id = get_queried_object_id()) {
    $uri = trim((string) get_page_uri($id), '/'); // e.g. "southeast-asia/singapore"
    if ($uri !== '') { $parts = explode('/', $uri); }
  }
  if (!$parts) {
    $path  = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    $parts = $path ? explode('/', $path) : [];
  }
  $parts = array_map(static fn($p) => sanitize_title((string) $p), $parts);
  return array_values(array_filter($parts, static fn($p) => $p !== ''));
}

function child_normalize_region_slug(string $slug): string {
  $slug = sanitize_title($slug);
  $aliases = [
    'south-east-asia' => 'southeast-asia',
    'sea'             => 'southeast-asia',
    'eu'              => 'europe',
  ];
  return $aliases[$slug] ?? $slug;
}

/** Find region + optional country anywhere in the path */
function child_resolve_region_country(): array {
  $parts  = child_route_parts();
  $known  = ['southeast-asia', 'europe'];

  $region  = '';
  $country = '';

  foreach ($parts as $i => $part) {
    $candidate = child_normalize_region_slug($part);
    if (in_array($candidate, $known, true)) {
      $region  = $candidate;
      $country = sanitize_title($parts[$i + 1] ?? '');
      break;
    }
  }

  if (!$region && isset($parts[0])) {
    $region  = child_normalize_region_slug($parts[0]);
    $country = sanitize_title($parts[1] ?? '');
  }

  return [$region, $country];
}

/** -------- file resolution + loader -------- */

function child_get_data_file(): ?string {
  $base = trailingslashit(get_stylesheet_directory()) . 'data/';

  [$region, $country] = child_resolve_region_country();

  // 1) country JSON in /data (e.g. data/singapore.json)
  if ($country) {
    $country_file = $base . $country . '.json';
    if (file_exists($country_file)) { return $country_file; }
  }

  // 2) region JSON
  $region_map = [
    'southeast-asia' => $base . 'sea.json',
    'europe'         => $base . 'europe.json',
  ];
  if ($region && isset($region_map[$region]) && file_exists($region_map[$region])) {
    return $region_map[$region];
  }

  return null;
}

function child_load_region_data(): array {
  $file = child_get_data_file();
  if (!$file) {
    error_log('[region-data] No JSON for route: ' . implode('/', child_route_parts()));
    return [];
  }
  $json = file_get_contents($file);
  $data = json_decode($json, true);
  if (!is_array($data)) {
    error_log("[region-data] Invalid JSON in {$file}");
    return [];
  }
  return $data;
}
