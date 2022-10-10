# Configuration

The complete bundle configuration: 
  
```yaml
# Default configuration for extension with alias: "huh_encore"
huh_encore:

  # Use contao template variables in fe_page (stylesheets, head, mootools) for inserting assets instead of the custom template variables of this bundle. Recommended: true. Default: false (due bc reasons). Will be default true in next major version.
  use_contao_template_variables: false

  # Remove jQuery from global array, if addJQuery is enabled in layout section.
  unset_jquery:         false
```