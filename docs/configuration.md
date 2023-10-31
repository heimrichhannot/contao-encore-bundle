# Configuration

The complete bundle configuration: 
  
```yaml
# Default configuration for extension with alias: "huh_encore"
huh_encore:
  templates:

    # Register import templates to customize how assets are imported into your templates.
    imports:

      # Prototype
      -

        # Unique template alias. Example: default_css
        name:                 ~ # Required

        # Full references twig template path. Example: @HeimrichHannotEncore/encore_css_imports.html.twig
        template:             ~ # Required

  # Remove jQuery from global array, if addJQuery is enabled in layout section.
  unset_jquery:         false
```