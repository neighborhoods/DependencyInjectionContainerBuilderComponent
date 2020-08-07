# Neighborhoods Container Builder
## Design Goals
* smallest possible scope
* discover, merge, and inject DI files into Symfony Container Builder
* provide the most possible safe access to the Symfony Container Builder
* gives a simple interface to cache
* gives an interface to turn cache off (default on)
* expose the ability make a service public
* expose the ability to dynamically set a service
* as much as possible provide both an PHP and env var API

## Ideas
* this looks like it should be a builder?
