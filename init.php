<?php
/*
Plugin Name: BFX crypto map
Plugin URI: https://bitfinex.com
description: BFX crypto map
Version: 1.0
Author: BFX
Author URI: https://bitfinex.com
License: GPL2
*/

// [bfx_crypto_map width="100%" height="100%" mode="desktop"]
function bfx_crypto_map_handler( $atts ) {
  $mapped_atts = shortcode_atts( array(
    'width' => '500px',
    'height' => '500px',
    'mobile_width' => '100%',
    'mobile_height' => 'calc(100vh - 100px)',
  ), $atts);

  $map_w = $mapped_atts['width'];
  $map_h = $mapped_atts['height'];
  $map_mobile_w = $mapped_atts['mobile_width'];
  $map_mobile_h = $mapped_atts['mobile_height'];
  $merchants_data_url = plugin_dir_url(__FILE__) . 'assets/merchants.json';
  $asset_url = plugin_dir_url(__FILE__) . 'assets';

  $html = <<<HTML
  <div class="bfx-crypto-container">
    <div class="bfx-crypto-filter">
      <div class="search-container">
        <input type="search" placeholder="Search" />
      </div>
      <button type="button">
        <img src="$asset_url/filter.png" />
        <span>Filter by</span>
      </button>
    </div>
    <div id="bfx-crypto-map"></div>
  </div>

  <div id="bfx-crypto-popup-template" style="display: none">
    <div class="bfx-marker-popup">
      <div class="header">
        <div class="logo">
        </div>
        <div>
          <div class="title"></div>
          <div class="description"></div>
        </div>
      </div>
      <div class="footer">
        <div class="label">Accepted Tokens</div>
        <div class="footer-container">
          <div class="tokens">
          </div>
          <div class="website">
          </div>
        </div>
      </div>
    </div>
  </div>
  <style>
    #bfx-crypto-map {
      width: $map_w;
      height: $map_h;
    }

    .bfx-crypto-filter {
      max-width: $map_w;
    }

    @media screen and (max-width: 768px) {
      #bfx-crypto-map {
        height: $map_mobile_h;
        width: $map_mobile_w;
      }
    }

  </style>
  <script>
    var MERCHANT_DATA = [];
    var markers = [];
    const isMobile = document.body.clientWidth < 768;
    const logoPlaceholder = 'https://via.placeholder.com/50x50';
    const tokenMap = {
      BTC: {
        name: 'BTC Lightning',
        width: 25,
        height: 22,
        icon: '$asset_url/BTC.png',
      },
      UST: {
        name: 'USDt',
        width: 22,
        height: 22,
        icon: '$asset_url/UST.png',
      },
      LVGA: {
        name: 'LVGA',
        width: 22,
        height: 22,
        icon: '$asset_url/LVGA.png',
      },
    };

    const map = L
      .map('bfx-crypto-map', {
        zoomControl: false,
      })
      .setView([46.005314, 8.953802], 17);

    L.control.zoom({ position: 'topright' }).addTo(map);

    const tiles = L
      .tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      })
      .addTo(map);

    const markerIcon = L.icon({
      iconUrl: '$asset_url/marker-icon.png',
      iconSize: [20, 20],
      iconAnchor: [10, 20],
      popupAnchor: [1, -20],
    });

    function setPopupContent(e, content) {
      const markerPopup = e.target.getPopup();

      if (markerPopup) {
        return markerPopup.setContent(content);
      }

      const width = map.getSize().x - 60; // 60 is the padding of the popup
      const bounds = map.getBounds();
      const center = bounds.getCenter();
      const south = bounds.getSouth();
      const popupLatLng = L.latLng(south, center.lng);

      return L
        .popup({
          autoPan: false,
          closeButton: false,
          maxWidth: width,
          minWidth: width,
          className: 'bfx-mobile-popup',
          keepInView: true,
        })
        .setLatLng(popupLatLng)
        .setContent(content)
        .openOn(map);
    }

    function onMarkerClick(e) {
      const merchant = MERCHANT_DATA.find(function (merchant) {
        return merchant.id === e.target.options.merchantId;
      });

      if (merchant) {
        const tags = (merchant.tags || []).map(function (tag) {
          return '<span class="tag">' + tag + '</span>';
        }).join('');
        const tokens = (merchant.accepted_cryptos || []).map(function (token) {
          const tokenInfo = tokenMap[token];
          if (tokenInfo) {
            const img = '<img src="' + tokenInfo.icon + '" width="' + tokenInfo.width +'" height="' + tokenInfo.height + '" />';
            const label = '<span>' + tokenInfo.name + '</span>';
            return '<div class="token">' + img + label + '</div>';
          }
          return '';
        }).join('');

        const logoUrl = merchant.logo_url || logoPlaceholder;
        const logo = '<img src="' + logoUrl + '" width="44" height="44" />';
        const titleStr = merchant.title || '';
        const title = '<h3>' + titleStr + '</h3>' + tags;
        const description = merchant.address ? '<p>' + merchant.address + '</p>' : '';
        const website = merchant.website
          ? '<a href="' + merchant.website + '" target="_blank"><img src="$asset_url/globe.png" /></a>'
          : '';

        const popupTemplate = document.getElementById('bfx-crypto-popup-template');
        popupTemplate.querySelector('.logo').innerHTML = logo;
        popupTemplate.querySelector('.title').innerHTML = title;
        popupTemplate.querySelector('.description').innerHTML = description;
        popupTemplate.querySelector('.tokens').innerHTML = tokens;
        popupTemplate.querySelector('.website').innerHTML = website;

        setPopupContent(e, popupTemplate.innerHTML);
      }
    }

    function clearMarkers() {
      markers.forEach(function(marker) {
        map.removeLayer(marker);
      });
      markers = [];
    }

    function renderMarkers(data) {
      clearMarkers();
      const popupOptions = {
        autoPanPadding: L.point(70, 70),
        maxWidth: 340,
        minWidth: 340,
        closeButton: false,
      };

      markers = data.map(function (merchant) {
        const marker = L
          .marker(
            [merchant.lat, merchant.lng],
            {
              merchantId: merchant.id,
              icon: markerIcon,
            },
          )
          .addTo(map)
          .on('click', onMarkerClick);

        if (!isMobile) {
          marker.bindPopup('', popupOptions)
        }

        return marker;
      });
    }

    jQuery
      .ajax({ url: '$merchants_data_url' })
      .done(function(data) {
        MERCHANT_DATA = data;
        renderMarkers(data);
      });
  </script>
  HTML;

  return $html;
}

function add_style_attributes( $html, $handle ) {
  if ( 'leaflet' === $handle ) {
    return str_replace( "media='all'", "media='all' integrity='sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=' crossorigin=''", $html );
  }

  return $html;
}

function add_script_attributes( $html, $handle ) {
  if ( 'leaflet' === $handle ) {
    return str_replace( "type='text/javascript'", "type='text/javascript' integrity='sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=' crossorigin=''", $html );
  }

  return $html;
}

function bfx_crypto_map_shortcode_scripts() {
  global $post;
  if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'bfx_crypto_map') ) {
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null);
    wp_enqueue_script('leaflet-marker-cluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js', array(), null);
    wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), null);
    wp_enqueue_style( 'leaflet-custom', plugin_dir_url(__FILE__) . 'assets/styles.css', array('leaflet'), null);
  }
}

add_action( 'wp_enqueue_scripts', 'bfx_crypto_map_shortcode_scripts');
add_filter( 'style_loader_tag', 'add_style_attributes', 10, 2);
add_filter( 'script_loader_tag', 'add_script_attributes', 10, 2);
add_shortcode( 'bfx_crypto_map', 'bfx_crypto_map_handler' );
?>
