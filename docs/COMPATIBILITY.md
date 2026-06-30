# Compatibility Matrix

| Component | Version |
|-----------|---------|
| MediaWiki | 1.39.x |
| PHP | 8.1 |
| Apache | 2.4 |
| MySQL | 8.0 |
| SemanticMediaWiki | 4.2 |
| SemanticResultFormats | Knowledge-Wiki fork |
| Validator | Legacy |
| ParamProcessor | Legacy compatible |
| Maps | ProfessionalWiki |
| ModernTimeline | 4.0.0 |
| Bootstrap | Custom |
| Kma | Custom |
| Vector | REL1_39 |

## Notes

- Do not replace legacy Validator.
- Do not run Composer inside SemanticResultFormats.
- Preserve SRF namespace patch.
- Preserve current production behaviour.
