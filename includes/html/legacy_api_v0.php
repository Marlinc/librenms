<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Config;

$init_modules = array('web', 'alerts');
require realpath(__DIR__ . '/..') . '/init.php';

if (!Auth::check()) {
    die('Unauthorized');
}

$app = new \Slim\Slim();

if (Config::get('api.cors.enabled') === true) {
    $corsOptions = array(
        "origin" => Config::get('api.cors.origin'),
        "maxAge" => Config::get('api.cors.maxage'),
        "allowMethods" => Config::get('api.cors.allowmethods'),
        "allowHeaders" => Config::get('api.cors.allowheaders'),
    );
    $cors = new \CorsSlim\CorsSlim($corsOptions);
    $app->add($cors);
}

require $config['install_dir'] . '/includes/html/api_functions.inc.php';
$app->setName('api');

$app->notFound(function () use ($app) {
    api_error(404, "This API route doesn't exist.");
});

$app->group(
    '/api',
    function () use ($app) {
        $app->group(
            '/v0',
            function () use ($app) {
                $app->get('/system', 'authToken', 'server_info')->name('server_info');
                $app->get('/bgp', 'authToken', 'list_bgp')->name('list_bgp');
                $app->get('/bgp/:id', 'authToken', 'get_bgp')->name('get_bgp');
                $app->get('/ospf', 'authToken', 'list_ospf')->name('list_ospf');
                // api/v0/bgp
                $app->get('/oxidized(/:hostname)', 'authToken', 'list_oxidized')->name('list_oxidized');
                $app->group(
                    '/devices',
                    function () use ($app) {
                        $app->delete('/:hostname', 'authToken', 'del_device')->name('del_device');
                        // api/v0/devices/$hostname
                        $app->get('/:hostname', 'authToken', 'get_device')->name('get_device');
                        // api/v0/devices/$hostname
                        $app->patch('/:hostname', 'authToken', 'update_device')->name('update_device_field');
                        $app->patch('/:hostname/rename/:new_hostname', 'authToken', 'rename_device')->name('rename_device');
                        $app->get('/:hostname/vlans', 'authToken', 'get_vlans')->name('get_vlans');
                        // api/v0/devices/$hostname/vlans
                        $app->get('/:hostname/links', 'authToken', 'list_links')->name('get_links');
                        // api/v0/devices/$hostname/links
                        $app->get('/:hostname/graphs', 'authToken', 'get_graphs')->name('get_graphs');
                        // api/v0/devices/$hostname/graphs
                        $app->get('/:hostname/fdb', 'authToken', 'get_fdb')->name('get_fdb');
                        // api/v0/devices/$hostname/fdb
                        $app->get('/:hostname/health(/:type)(/:sensor_id)', 'authToken', 'list_available_health_graphs')->name('list_available_health_graphs');
                        $app->get('/:hostname/wireless(/:type)(/:sensor_id)', 'authToken', 'list_available_wireless_graphs')->name('list_available_wireless_graphs');
                        $app->get('/:hostname/ports', 'authToken', 'get_port_graphs')->name('get_port_graphs');
                        $app->get('/:hostname/ip', 'authToken', 'get_ip_addresses')->name('get_device_ip_addresses');
                        $app->get('/:hostname/port_stack', 'authToken', 'get_port_stack')->name('get_port_stack');
                        // api/v0/devices/$hostname/ports
                        $app->get('/:hostname/components', 'authToken', 'get_components')->name('get_components');
                        $app->post('/:hostname/components/:type', 'authToken', 'add_components')->name('add_components');
                        $app->put('/:hostname/components', 'authToken', 'edit_components')->name('edit_components');
                        $app->delete('/:hostname/components/:component', 'authToken', 'delete_components')->name('delete_components');
                        $app->get('/:hostname/groups', 'authToken', 'get_device_groups')->name('get_device_groups');
                        $app->get('/:hostname/graphs/health/:type(/:sensor_id)', 'authToken', 'get_graph_generic_by_hostname')->name('get_health_graph');
                        $app->get('/:hostname/graphs/wireless/:type(/:sensor_id)', 'authToken', 'get_graph_generic_by_hostname')->name('get_wireless_graph');
                        $app->get('/:hostname/:type', 'authToken', 'get_graph_generic_by_hostname')->name('get_graph_generic_by_hostname');
                        // api/v0/devices/$hostname/$type
                        $app->get('/:hostname/ports/:ifname', 'authToken', 'get_port_stats_by_port_hostname')->name('get_port_stats_by_port_hostname');
                        // api/v0/devices/$hostname/ports/$ifName
                        $app->get('/:hostname/ports/:ifname/:type', 'authToken', 'get_graph_by_port_hostname')->name('get_graph_by_port_hostname');
                        // api/v0/devices/$hostname/ports/$ifName/$type
                    }
                );
                $app->get('/devices', 'authToken', 'list_devices')->name('list_devices');
                // api/v0/devices
                $app->post('/devices', 'authToken', 'add_device')->name('add_device');
                // api/v0/devices (json data needs to be passed)
                $app->group(
                    '/devicegroups',
                    function () use ($app) {
                        $app->get('/:name', 'authToken', 'get_devices_by_group')->name('get_devices_by_group');
                    }
                );
                $app->get('/devicegroups', 'authToken', 'get_device_groups')->name('get_devicegroups');
                $app->group(
                    '/ports',
                    function () use ($app) {
                        $app->get('/:portid', 'authToken', 'get_port_info')->name('get_port_info');
                        $app->get('/:portid/ip', 'authToken', 'get_ip_addresses')->name('get_port_ip_info');
                    }
                );
                $app->get('/ports', 'authToken', 'get_all_ports')->name('get_all_ports');
                $app->group(
                    '/portgroups',
                    function () use ($app) {
                        $app->get('/multiport/bits/:id', 'authToken', 'get_graph_by_portgroup')->name('get_graph_by_portgroup_multiport_bits');
                        $app->get('/:group', 'authToken', 'get_graph_by_portgroup')->name('get_graph_by_portgroup');
                    }
                );
                $app->group(
                    '/bills',
                    function () use ($app) {
                        $app->get('/:bill_id', 'authToken', 'list_bills')->name('get_bill');
                        $app->delete('/:id', 'authToken', 'delete_bill')->name('delete_bill');
                        // api/v0/bills/$bill_id
                        $app->get('/:bill_id/graphs/:graph_type', 'authToken', 'get_bill_graph')->name('get_bill_graph');
                        $app->get('/:bill_id/graphdata/:graph_type', 'authToken', 'get_bill_graphdata')->name('get_bill_graphdata');
                        $app->get('/:bill_id/history', 'authToken', 'get_bill_history')->name('get_bill_history');
                        $app->get('/:bill_id/history/:bill_hist_id/graphs/:graph_type', 'authToken', 'get_bill_history_graph')->name('get_bill_history_graph');
                        $app->get('/:bill_id/history/:bill_hist_id/graphdata/:graph_type', 'authToken', 'get_bill_history_graphdata')->name('get_bill_history_graphdata');
                    }
                );
                $app->get('/bills', 'authToken', 'list_bills')->name('list_bills');
                $app->post('/bills', 'authToken', 'create_edit_bill')->name('create_bill');
                // api/v0/bills
                // /api/v0/alerts
                $app->group(
                    '/alerts',
                    function () use ($app) {
                        $app->get('/:id', 'authToken', 'list_alerts')->name('get_alert');
                        // api/v0/alerts
                        $app->put('/:id', 'authToken', 'ack_alert')->name('ack_alert');
                        // api/v0/alerts/$id (PUT)
                        $app->put('/unmute/:id', 'authToken', 'unmute_alert')->name('unmute_alert');
                        // api/v0/alerts/unmute/$id (PUT)
                    }
                );
                $app->get('/alerts', 'authToken', 'list_alerts')->name('list_alerts');
                // api/v0/alerts
                // /api/v0/rules
                $app->group(
                    '/rules',
                    function () use ($app) {
                        $app->get('/:id', 'authToken', 'list_alert_rules')->name('get_alert_rule');
                        // api/v0/rules/$id
                        $app->delete('/:id', 'authToken', 'delete_rule')->name('delete_rule');
                        // api/v0/rules/$id (DELETE)
                    }
                );
                $app->get('/rules', 'authToken', 'list_alert_rules')->name('list_alert_rules');
                // api/v0/rules
                $app->post('/rules', 'authToken', 'add_edit_rule')->name('add_rule');
                // api/v0/rules (json data needs to be passed)
                $app->put('/rules', 'authToken', 'add_edit_rule')->name('edit_rule');
                // api/v0/rules (json data needs to be passed)
                // Inventory section
                $app->group(
                    '/inventory',
                    function () use ($app) {
                        $app->get('/:hostname', 'authToken', 'get_inventory')->name('get_inventory');
                    }
                );
                // End Inventory
                // Routing section
                $app->group(
                    '/routing',
                    function () use ($app) {
                        $app->get('/bgp/cbgp', 'authToken', 'list_cbgp')->name('list_cbgp');
                        $app->get('/vrf', 'authToken', 'list_vrf')->name('list_vrf');
                        $app->get('/vrf/:id', 'authToken', 'get_vrf')->name('get_vrf');
                        $app->group(
                            '/ipsec',
                            function () use ($app) {
                                $app->get('/data/:hostname', 'authToken', 'list_ipsec')->name('list_ipsec');
                            }
                        );
                    }
                );
            // End Routing
                // Resources section
                $app->group(
                    '/resources',
                    function () use ($app) {
                        $app->get('/fdb/', 'authToken', 'list_fdb')->name('list_fdb');
                        $app->get('/fdb/:mac', 'authToken', 'list_fdb')->name('list_fdb_mac');
                        $app->get('/links', 'authToken', 'list_links')->name('list_links');
                        $app->get('/links/:id', 'authToken', 'get_link')->name('get_link');
                        $app->get('/locations', 'authToken', 'list_locations')->name('list_locations');
                        $app->get('/sensors', 'authToken', 'list_sensors')->name('list_sensors');
                        $app->get('/vlans', 'authToken', 'list_vlans')->name('list_vlans');
                        $app->group(
                            '/ip',
                            function () use ($app) {
                                $app->get('/addresses/', 'authToken', 'list_ip_addresses')->name('list_ip_addresses');
                                $app->get('/arp/:ip', 'authToken', 'list_arp')->name('list_arp')->conditions(array('ip' => '[^?]+'));
                                $app->get('/networks/', 'authToken', 'list_ip_networks')->name('list_ip_networks');
                                $app->get('/networks/:id/ip', 'authToken', 'get_ip_addresses')->name('get_network_ip_addresses');
                            }
                        );
                    }
                );
                // End Resources
                // Service section
                $app->group(
                    '/services',
                    function () use ($app) {
                        $app->get('/:hostname', 'authToken', 'list_services')->name('get_service_for_host');
                        $app->post('/:hostname', 'authToken', 'add_service_for_host')->name('add_service_for_host');
                    }
                );
                $app->get('/services', 'authToken', 'list_services')->name('list_services');
                // End Service
                $app->group(
                    '/logs',
                    function () use ($app) {
                        $app->get('/eventlog(/:hostname)', 'authToken', 'list_logs')->name('list_eventlog');
                        $app->get('/syslog(/:hostname)', 'authToken', 'list_logs')->name('list_syslog');
                        $app->get('/alertlog(/:hostname)', 'authToken', 'list_logs')->name('list_alertlog');
                        $app->get('/authlog(/:hostname)', 'authToken', 'list_logs')->name('list_authlog');
                    }
                );
            }
        );
        $app->get('/v0', 'authToken', 'show_endpoints');
        // api/v0
    }
);

$app->run();
