# Azebo
Azebo is a Web-Application to record working times of employees. It was originally started
by the [Kunsthochschule Berlin Weißensee](https://www.kh-berlin.de/), the [Hochschule
für Musik Hanns Eisler Berlin](https://www.hfm-berlin.de/) and the [Hochschule für
Schauspielkunst Ernst Busch](https://www.hfs-berlin.de/index.html). It is meant to
create PDF documents with monthly working times for each employee. It runs on a
[LAMP](https://en.wikipedia.org/wiki/LAMP_software_bundle) architecture. The name
is an acronym for the german "**A**rbeits**ze**iterfassungs**bo**gen".

## Getting Started
### Dependencies
Azebo depends depends on the LAMP-Stack with following versions:

- Linux (>= 4.19.27)
- Apache (>= 2.4.39)
- MariaDB (>= 10.2.22)
- PHP (>= 7.2.16)

It depends also on [Zend Framework 1.12](https://github.com/zendframework/zf1) and the
[Dojo Toolkit](https://dojotoolkit.org/) in version 1.10.2. These versions are no longer
maintained, are security problems and are first priority to be updated.

### Installing
Azebo ist installed as any other LAMP Application.
<!--- TODO Installationsanweisung schreiben! --->
#### Configure Apache
Make sure your apache has the modules `mod_php`, `mod_rewrite` and `mod_alias` installed
and enabled. Configure your `vhost` for example with
[000-azebo.conf](https://github.com/emanuel-minetti/azebo/blob/azebo-1/resources/configs/000-azebo.conf).
Adjust that file to your used paths.
Copy `/aspplication/application.php.dist` to `/aspplication/application.php` and adjust paths.


## Contributing
Feel free to e-mail me in case you want to contribute.

## License
This software is licensed under the GPLv3 - see the
[License](https://github.com/emanuel-minetti/azebo/blob/master/LICENSE) file
for details.

## TODOs
- Update project to use PHP 7.* and Zend Framework 2.
- Create a REST API.
- Rewrite as a SPA (Single Page Application) using Angular
