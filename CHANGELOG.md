# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of Laravel Plugin System
- Comprehensive plugin architecture for Laravel applications

## [2.1.0] - 2025-12-28

### Added
- **Plugin List Command**: Added `php artisan plugin:list` to view all plugins.
- **Manifest Tracking**: Added support for tracking whether plugins are loaded from cache or scanned.

## [2.0.0] - 2024-03-20

### Added
- **Middleware Support**: Added ability to create middleware in plugins (`--middleware`)
- **Language Files**: Added ability to create language files in plugins (`--lang`)
- **Plugin Manager**: Enhanced plugin discovery and registration
- **Blade Hooks**: Added support for blade hooks

## [1.0.0] - 2024-01-15

### Added
- **Core Plugin System**
    - Automatic Plugin Discovery
    - Plugin Manager
    - Service Provider Integration
- **Route Management**
    - Auto Route Registration
    - Route Prefixing
    - Route Isolation
- **Controller System**
    - Automatic Controller Binding
    - Namespace Resolution
    - Laravel Integration
- **Service Architecture**
    - Service Registration
    - Interface Binding
    - Dependency Injection
- **View Integration**
    - Livewire Volt Support
    - Blade Templates
    - View Namespace
    - Auto View Type Detection
- **Configuration Management**
    - Auto Config Loading
    - Config Publishing
    - Environment-based Config
- **Developer Tools**
    - Plugin Generator Command (`php artisan make:plugin`)
    - Multiple View Types
    - Boilerplate Generation

### Technical Specifications
- PHP ^8.1
- Laravel ^10.0|^11.0|^12.0
- Livewire Volt ^1.0 (optional)
