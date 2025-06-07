<?php
/**
 * phpMyAdmin configuration for Console Project
 */

$cfg['blowfish_secret'] = 'Console-RAG-phpMyAdmin-Secret-Key-2025';

/**
 * Servers configuration
 */
$i = 0;

/**
 * First server (MySQL)
 */
$i++;
$cfg['Servers'][$i]['auth_type'] = 'cookie'; // Changed from 'config' to 'cookie'
$cfg['Servers'][$i]['host'] = '127.0.0.1';
$cfg['Servers'][$i]['connect_type'] = 'tcp';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['extension'] = 'mysqli';
$cfg['Servers'][$i]['user'] = 'root';
$cfg['Servers'][$i]['password'] = '';
$cfg['Servers'][$i]['AllowNoPassword'] = true;
/**
 * Other configuration
 */
$cfg['DefaultLang'] = 'en';
$cfg['ServerDefault'] = 1;
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
$cfg['CheckConfigurationPermissions'] = false;