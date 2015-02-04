![Logo of phpMemAdmin](docs/logo-large.png)
Bringing `Memcached` to the web

  
| [![Build Status](https://travis-ci.org/clickalicious/phpMemAdmin.svg?branch=master)](https://travis-ci.org/clickalicious/phpMemAdmin) 	| [![Scrutinizer](https://img.shields.io/scrutinizer/g/clickalicious/phpMemAdmin.svg)](https://scrutinizer-ci.com/g/clickalicious/phpMemAdmin/) 	| [![clickalicious premium](https://img.shields.io/badge/clickalicious-premium-green.svg?style=flat)](https://www.clickalicious.de/) 	| [![Packagist](https://img.shields.io/packagist/l/clickalicious/phpmemadmin.svg?style=flat)](http://opensource.org/licenses/BSD-3-Clause) 	|
|---	|---	|---	|---	|
| [![GitHub issues](https://img.shields.io/github/issues/clickalicious/phpmemadmin.svg?style=flat)](https://github.com/clickalicious/phpMemAdmin/issues) 	| [![Coverage Status](https://coveralls.io/repos/clickalicious/phpMemAdmin/badge.svg)](https://coveralls.io/r/clickalicious/phpMemAdmin)  	| [![GitHub release](https://img.shields.io/github/release/clickalicious/phpMemAdmin.svg?style=flat)](https://github.com/clickalicious/phpMemAdmin/releases) 	| [![GitHub stars](https://img.shields.io/github/stars/clickalicious/phpmemadmin.svg?style=flat)](https://github.com/clickalicious/phpMemAdmin/stargazers)  	|

## Features

 - Detailed statistics, charts & graphs
 - Data management for `Memcached` (full CRUD support)
 - `Memcached` cluster health dashboard
 - `Memcached` host dashboard
 - Update check
 - Nice & comfortable UI
 - Clean & well documented code
 - Responsive and mobile ready


## Requirements

 - PHP >= 5.3 (compatible up to version 5.6 - but **not compatible** with *hhvm*)


## Philosophy

`phpMemAdmin` was designed as helper while developing a cache in PHP using Memcached as in memory store. I had a need for a tool which displays me modified data stored in Memcached for example. The existing tools did not provide a UI i would describe as usable so i wrote my own. Trying to align it with the `P


## Installation
The recommended way to install this tool is through [Composer](http://getcomposer.org/). Require the `clickalicious/phpmemadmin` package into your `composer.json` file:

```json
{
    "require": {
        "clickalicious/phpmemadmin": "~0.1"
    }
}
```
**phpMemAdmin** is also available as [download from github packed as zip-file](https://github.com/clickalicious/phpMemAdmin/archive/master.zip "zip package containing library for download") or via `git clone https://github.com/clickalicious/phpMemAdmin.git .`


## Screenshots

| Cluster Dashboard |
|:---:|
| ![Cluster Dashboard](docs/phpMemAdmin_01-small.png) |
 

----------


| Host Dashboard |
|:---:|
| ![Host Dashboard](docs/phpMemAdmin_02-small.png) |


----------


| Data Management |
|:---:|
| ![Host Dashboard](docs/phpMemAdmin_03-small.png) |


## Documentation

There is currently no documentation.


## Versioning
For a consistent versioning i decided to make use of `Semantic Versioning 2.0.0` http://semver.org. Its easy to understand, very common and known from many other software projects. 


## Roadmap

- [ ] Move all assets from CDN to local filesystem as precondition for compiling everything into a single file app (requires an issue).
- [ ] Move "settings" from dashboard to an own page
- [ ] Add more checks to cluster health check
- [ ] Add Slab Statistics and Overview page


## Participate & share

... yeah. If you're a code monkey too - maybe we can build a force ;) If you would like to participate in either **Code**, **Comments**, **Documentation**, **Wiki**, **Bug-Reports**, **Unit-Tests**, **Bug-Fixes**, **Feedback** and/or **Critic** then please let me know as well!
<a href="https://twitter.com/intent/tweet?hashtags=&original_referer=http%3A%2F%2Fgithub.com%2F&text=%23phpMemAdmin%20-%20Bringing%20%40memcached%20to%20the%20web.%20https%3A%2F%2Fgithub.com%2Fclickalicious%2FphpMemAdmin&tw_p=tweetbutton" target="_blank">
  <img src="http://jpillora.com/github-twitter-button/img/tweet.png"></img>
</a>


## Author

| [![Benjamin Carl](http://de.gravatar.com/userimage/10744805/d0a6316a34accd0f2921519dfe4dee48.jpg?size=100)](http://www.clickalicious.de) |
|---|
| [Benjamin Carl](http://www.clickalicious.de) |

## Sponsors  
Thanks to our sponsors and supporters:  
<a href="https://www.jetbrains.com/phpstorm/" title="PHP IDE :: JetBrains PhpStorm" target="_blank">
    <img src="https://www.jetbrains.com/phpstorm/documentation/docs/logo_phpstorm.png"></img>
</a>
