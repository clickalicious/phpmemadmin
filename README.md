phpMemAdmin
===========

Bringing `Memcached` to the web.

![Logo of phpMemAdmin](docs/logo.png)

[![Build Status](https://travis-ci.org/clickalicious/phpMemAdmin.svg?branch=master)](https://travis-ci.org/clickalicious/phpMemAdmin)
<a href="https://twitter.com/intent/tweet?hashtags=&original_referer=http%3A%2F%2Fgithub.com%2F&text=%23phpMemAdmin%20-%20Bringing%20%40memcached%20to%20the%20web.%20https%3A%2F%2Fgithub.com%2Fclickalicious%2FphpMemAdmin&tw_p=tweetbutton" target="_blank">
  <img src="http://jpillora.com/github-twitter-button/img/tweet.png"></img>
</a>


## Features

 - Detailed statistics, charts & graphs
 - Data management for `Memcached` (full CRUD support)
 - `Memcached` cluster health dashboard
 - `Memcached` host dashboard
 - Update check
 - Nice & comfortable UI
 - Clean & well documented code
 - Responsive and mobile ready


## Cluster Dashboard
![Cluster Dashboard](docs/phpMemAdmin_01-small.png)


## Host Dashboard
![Host Dashboard](docs/phpMemAdmin_02-small.png)


## Data Management
![Host Dashboard](docs/phpMemAdmin_03-small.png)


## Install
### Requirements
 - PHP >= 5.3 (compatible up to version 5.6 - but **not compatible** with *hhvm*)

The recommended way to install this tool is through [Composer](http://getcomposer.org/). Require the `clickalicious/phpmemadmin` package into your `composer.json` file:

```json
{
    "require": {
        "clickalicious/phpmemadmin": "~0.1"
    }
}
```

**phpMemAdmin** is also available as [download from github packed as zip-file](https://github.com/clickalicious/phpMemAdmin/archive/master.zip "zip package containing library for download") or via `git clone https://github.com/clickalicious/phpMemAdmin.git .`.


## Roadmap

- [ ] Move all assets from CDN to local filesystem as precondition for compiling everything into a single file app (requires an issue).


## License
**BSD-3-Clause** 
See [BSD-3-Clause](http://opensource.org/licenses/BSD-3-Clause "BSD-3-Clause") or LICENSE file for details.


## Credit

| [![Benjamin Carl](http://www.gravatar.com/avatar/4d97670d59ce760bd7c117a1dfe3cf43.png)](http://www.clickalicious.de) |
|---|
| [Benjamin Carl](http://www.clickalicious.de) (creator) |


## Sponsors  
Thanks to our sponsors and supporters:  
<a href="https://www.jetbrains.com/phpstorm/" title="PHP IDE :: JetBrains PhpStorm" target="_blank">
    <img src="https://www.jetbrains.com/phpstorm/documentation/docs/logo_phpstorm.png"></img>
</a>
