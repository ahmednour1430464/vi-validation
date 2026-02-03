# Schema Definition

<cite>
**Referenced Files in This Document**
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php)
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php)
- [CompiledSchema.php](file://src/Execution/CompiledSchema.php)
- [CompiledField.php](file://src/Execution/CompiledField.php)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php)
- [ValidationContext.php](file://src/Execution/ValidationContext.php)
- [DataHelper.php](file://src/Execution/DataHelper.php)
- [ConditionalRule.php](file://src/Rules/ConditionalRule.php)
- [MissingRule.php](file://src/Rules/MissingRule.php)
- [PresentRule.php](file://src/Rules/PresentRule.php)
- [RequiredIfRule.php](file://src/Rules/RequiredIfRule.php)
- [SchemaCacheInterface.php](file://src/Cache/SchemaCacheInterface.php)
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php)
- [benchmark.php](file://tests/benchmark.php)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [Dependency Analysis](#dependency-analysis)
7. [Performance Considerations](#performance-considerations)
8. [Troubleshooting Guide](#troubleshooting-guide)
9. [Conclusion](#conclusion)
10. [Appendices](#appendices)

## Introduction
This document explains how to define validation schemas in vi/validation, focusing on the SchemaBuilder API, field definition syntax, nested field support via dot notation, and conditional validation rules. It covers field chaining patterns, rule application order, validation context, advanced features such as conditional required rules, missing field validation, and field presence checking. Practical examples demonstrate constructing complex schemas for real-world scenarios, along with the schema compilation process, optimization benefits, reuse patterns, versioning, inheritance, and best practices for maintainable validation logic.

## Project Structure
The schema system centers around a fluent builder that produces a compiled representation consumed by an execution engine. Supporting components include rule implementations, a native compiler for optimized runtime, and caching interfaces for reuse.

```mermaid
graph TB
subgraph "Schema Definition"
SB["SchemaBuilder<br/>Defines fields and rules"]
FD["FieldDefinition<br/>Fluent rule builders"]
end
subgraph "Compilation"
CS["CompiledSchema<br/>Container of CompiledField"]
CF["CompiledField<br/>Dedup + reorder rules"]
NC["NativeCompiler<br/>Generate optimized PHP"]
VC["ValidatorCompiler<br/>Compile + cache"]
end
subgraph "Execution"
VE["ValidatorEngine<br/>Run compiled schema"]
CTX["ValidationContext<br/>Access data + helpers"]
DH["DataHelper<br/>Dot notation access"]
end
SB --> FD
FD --> CS
CS --> CF
VC --> CS
VC --> NC
CS --> VE
VE --> CTX
VE --> DH
```

**Diagram sources**
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php#L9-L35)
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L9-L657)
- [CompiledSchema.php](file://src/Execution/CompiledSchema.php#L9-L67)
- [CompiledField.php](file://src/Execution/CompiledField.php#L10-L176)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L17-L309)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L10-L194)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L11-L176)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L7-L97)
- [DataHelper.php](file://src/Execution/DataHelper.php#L10-L31)

**Section sources**
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php#L9-L35)
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L9-L657)
- [CompiledSchema.php](file://src/Execution/CompiledSchema.php#L9-L67)
- [CompiledField.php](file://src/Execution/CompiledField.php#L10-L176)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L17-L309)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L10-L194)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L11-L176)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L7-L97)
- [DataHelper.php](file://src/Execution/DataHelper.php#L10-L31)

## Core Components
- SchemaBuilder: Fluent entry point to define fields and compile to CompiledSchema.
- FieldDefinition: Fluent rule builder per field with extensive rule methods and conditional composition.
- CompiledSchema: Holds CompiledField instances and the rules array; exposes validate(data).
- CompiledField: Normalized field with deduplicated/reordered rules, nested access, and exclusion handling.
- ValidatorEngine: Executes CompiledSchema against data, honoring bail, nullable, sometimes, and implicit rules.
- ValidationContext: Provides accessors for nested data and presence checks used by rules.
- NativeCompiler: Generates optimized PHP code for validation, including dot notation handling and bail short-circuiting.
- ValidatorCompiler: Orchestrates object and native compilation, caching, and precompiled artifacts.

**Section sources**
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php#L9-L35)
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L9-L657)
- [CompiledSchema.php](file://src/Execution/CompiledSchema.php#L9-L67)
- [CompiledField.php](file://src/Execution/CompiledField.php#L10-L176)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L11-L176)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L7-L97)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L17-L309)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L10-L194)

## Architecture Overview
The schema definition lifecycle:
1. Define fields and rules using SchemaBuilder and FieldDefinition.
2. Compile to CompiledSchema (object form).
3. Optionally compile to native PHP code for runtime speed.
4. Execute CompiledSchema via ValidatorEngine, which uses ValidationContext and CompiledField behavior.

```mermaid
sequenceDiagram
participant Dev as "Developer"
participant SB as "SchemaBuilder"
participant FD as "FieldDefinition"
participant CS as "CompiledSchema"
participant VC as "ValidatorCompiler"
participant NC as "NativeCompiler"
participant VE as "ValidatorEngine"
Dev->>SB : "field(name)"
SB->>FD : "create FieldDefinition"
Dev->>FD : "chain rule methods"
Dev->>SB : "compile()"
SB->>CS : "fromFieldDefinitions(fields, rulesArray)"
Dev->>VC : "compile(key, rules, compiler)"
VC->>CS : "build schema object"
VC->>NC : "compile(schema) -> native code"
Dev->>CS : "validate(data)"
CS->>VE : "engine.validate(this, data)"
VE-->>Dev : "ValidationResult"
```

**Diagram sources**
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php#L14-L34)
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L640-L651)
- [CompiledSchema.php](file://src/Execution/CompiledSchema.php#L27-L66)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L33-L73)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L24-L51)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L33-L97)

## Detailed Component Analysis

### SchemaBuilder API and Field Chaining
- Create fields with field(name) and chain rule methods.
- Access nested fields using dot notation in the field name.
- Compile to CompiledSchema for execution.
- Set a rules array for global schema metadata.

```mermaid
classDiagram
class SchemaBuilder {
-array rulesArray
+field(name) FieldDefinition
+setRulesArray(rules) void
+compile() CompiledSchema
}
class FieldDefinition {
-string name
-RuleInterface[] rules
+required() self
+nullable() self
+bail() self
+sometimes() self
+string() self
+integer() self
+decimal(min, max) self
+numeric() self
+boolean() self
+array() self
+list() self
+distinct() self
+date(format) self
+json() self
+alpha() self
+alphaNum() self
+min(value) self
+max(value) self
+gt(otherField) self
+gte(otherField) self
+lt(otherField) self
+lte(otherField) self
+email() self
+url() self
+uuid() self
+ip(version) self
+regex(pattern) self
+size(value) self
+exists(table, column, extra) self
+unique(table, column, ignore, idCol, extra) self
+between(min, max) self
+in(values...) self
+notIn(values...) self
+confirmed() self
+same(field) self
+different(field) self
+file() self
+image() self
+mimes(types...) self
+maxFileSize(kb) self
+minFileSize(kb) self
+mimetypes(types...) self
+extensions(ext...) self
+dimensions(constraints) self
+rules(RuleInterface...) self
+activeUrl() self
+alphaDash() self
+macAddress() self
+ulid() self
+country() self
+language() self
+ascii() self
+uppercase() self
+enum(enumClass) self
+password(callback) self
+currentPassword() self
+lowercase() self
+dateFormat(format) self
+dateEquals(date) self
+multipleOf(value) self
+digits(value) self
+digitsBetween(min, max) self
+startsWith(needles...) self
+endsWith(needles...) self
+notRegex(pattern) self
+doesntStartWith(needles...) self
+doesntEndWith(needles...) self
+timezone() self
+requiredArrayKeys(keys...) self
+requiredIf(otherField, values) self
+requiredIfAccepted(otherField) self
+requiredUnless(otherField, values) self
+requiredWith(others...) self
+requiredWithAll(others...) self
+requiredWithout(others...) self
+requiredWithoutAll(others...) self
+present() self
+filled() self
+prohibitedIf(otherField, values) self
+prohibitedUnless(otherField, values) self
+acceptedIf(otherField, value) self
+declinedIf(otherField, value) self
+prohibits(fields...) self
+missing() self
+missingIf(otherField, value) self
+missingUnless(otherField, value) self
+missingWith(others...) self
+missingWithAll(others...) self
+exclude() self
+excludeIf(otherField, value) self
+excludeUnless(otherField, value) self
+excludeWith(otherField) self
+excludeWithout(otherField) self
+when(condition, onTrue, onFalse?) self
+field(name) self
+compile() CompiledSchema
+end() SchemaBuilder
}
SchemaBuilder --> FieldDefinition : "creates"
```

**Diagram sources**
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php#L9-L35)
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L9-L657)

**Section sources**
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php#L14-L34)
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L24-L622)

### Nested Field Support with Dot Notation
- FieldDefinition supports dot notation in field names (e.g., parent.child).
- CompiledField detects nested fields and extracts parent/child segments.
- DataHelper provides robust dot notation access for arbitrary nesting.
- ValidationContext.getValue and hasValue support depth-2 dot notation natively; deeper nesting falls back to DataHelper.

```mermaid
flowchart TD
Start(["Field name"]) --> CheckDot{"Contains '.'?"}
CheckDot --> |No| Simple["Direct data access"]
CheckDot --> |Yes| Split["Split into parent.child"]
Split --> ParentCheck{"Parent exists and is array?"}
ParentCheck --> |No| ReturnNull["Return null"]
ParentCheck --> |Yes| ChildCheck{"Child key exists?"}
ChildCheck --> |No| ReturnNull
ChildCheck --> |Yes| ReturnValue["Return child value"]
```

**Diagram sources**
- [CompiledField.php](file://src/Execution/CompiledField.php#L163-L175)
- [DataHelper.php](file://src/Execution/DataHelper.php#L15-L30)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L43-L73)

**Section sources**
- [CompiledField.php](file://src/Execution/CompiledField.php#L33-L38)
- [CompiledField.php](file://src/Execution/CompiledField.php#L163-L175)
- [DataHelper.php](file://src/Execution/DataHelper.php#L15-L30)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L43-L73)

### Conditional Validation Rules
- FieldDefinition.when(condition, onTrue, onFalse?) composes two sub-schemas and wraps them in ConditionalRule.
- ConditionalRule evaluates the condition (bool or callable) and applies either trueRules or elseRules.
- ValidationContext is passed to callables for data-aware conditions.

```mermaid
sequenceDiagram
participant FD as "FieldDefinition"
participant CR as "ConditionalRule"
participant Ctx as "ValidationContext"
participant R1 as "Rules (true)"
participant R2 as "Rules (false)"
FD->>CR : "wrap onTrue/onFalse rules"
CR->>Ctx : "evaluate condition(data, ctx)"
alt condition == true
CR->>R1 : "validate(value, field, ctx)"
else condition == false
CR->>R2 : "validate(value, field, ctx)"
end
```

**Diagram sources**
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L604-L622)
- [ConditionalRule.php](file://src/Rules/ConditionalRule.php#L36-L52)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L17-L21)

**Section sources**
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L604-L622)
- [ConditionalRule.php](file://src/Rules/ConditionalRule.php#L12-L69)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L37-L41)

### Rule Application Order and Implicit Rules
- CompiledField deduplicates and reorders rules to optimize execution:
  - Markers: bail, required, nullable (in that order)
  - Others: remaining rules
- ValidatorEngine skips non-implicit rules when values are considered “empty”.
- Implicit rules (required variants, accepted, present, prohibited, etc.) evaluate regardless of emptiness.

```mermaid
flowchart TD
A["Start field validation"] --> B{"Is rule implicit?"}
B --> |Yes| Apply["Apply rule even if empty"]
B --> |No| Empty{"Value empty?"}
Empty --> |Yes| Skip["Skip rule"]
Empty --> |No| Apply
Apply --> Bail{"Field has bail?"}
Bail --> |Yes| StopOnFirstErr["Stop on first error"]
Bail --> |No| Next["Next rule"]
```

**Diagram sources**
- [CompiledField.php](file://src/Execution/CompiledField.php#L50-L113)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L78-L94)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L127-L146)

**Section sources**
- [CompiledField.php](file://src/Execution/CompiledField.php#L50-L113)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L78-L94)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L127-L146)

### Validation Context and Presence Checking
- ValidationContext.getValue supports dot notation up to depth-2; deeper uses DataHelper.
- PresentRule validates that a field is present (exists) in the input data.
- MissingRule validates that a field is not present in the input data.
- RequiredIfRule conditionally requires a field based on another field’s value.

```mermaid
classDiagram
class ValidationContext {
+getValue(field) mixed
+hasValue(field) bool
+getData() array
+addError(field, rule, message?, params?) void
}
class PresentRule {
+validate(value, field, ctx) ?array
}
class MissingRule {
+validate(value, field, ctx) ?array
}
class RequiredIfRule {
+validate(value, field, ctx) ?array
}
ValidationContext <.. PresentRule : "used by"
ValidationContext <.. MissingRule : "used by"
ValidationContext <.. RequiredIfRule : "used by"
```

**Diagram sources**
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L43-L96)
- [PresentRule.php](file://src/Rules/PresentRule.php#L15-L38)
- [MissingRule.php](file://src/Rules/MissingRule.php#L12-L19)
- [RequiredIfRule.php](file://src/Rules/RequiredIfRule.php#L26-L48)

**Section sources**
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L43-L96)
- [PresentRule.php](file://src/Rules/PresentRule.php#L15-L38)
- [MissingRule.php](file://src/Rules/MissingRule.php#L12-L19)
- [RequiredIfRule.php](file://src/Rules/RequiredIfRule.php#L26-L48)

### Advanced Schema Features
- Conditional required rules:
  - requiredIf(otherField, values)
  - requiredUnless(otherField, values)
  - requiredWith(others...)
  - requiredWithAll(others...)
  - requiredWithout(others...)
  - requiredWithoutAll(others...)
  - requiredIfAccepted(otherField)
- Missing field validation:
  - missing()
  - missingIf(otherField, value)
  - missingUnless(otherField, value)
  - missingWith(others...)
  - missingWithAll(others...)
- Field presence checking:
  - present()
  - filled() (must be present and not empty)
- Exclusion rules:
  - exclude(), excludeIf(), excludeUnless(), excludeWith(), excludeWithout()

**Section sources**
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L460-L602)
- [PresentRule.php](file://src/Rules/PresentRule.php#L15-L38)
- [MissingRule.php](file://src/Rules/MissingRule.php#L12-L19)
- [RequiredIfRule.php](file://src/Rules/RequiredIfRule.php#L26-L48)

### Practical Examples
- Basic chained schema with nested fields and optional values.
- Real-world scenario: user profile with nested address and conditional phone requirement.

Example references:
- Basic chaining and nested fields: [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L14-L18), [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L53-L56)
- Nested validation failures: [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L68-L87)
- Nullable field behavior: [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L89-L99)
- Benchmark schema with nested fields: [benchmark.php](file://tests/benchmark.php#L15-L21)

**Section sources**
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L14-L18)
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L53-L56)
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L68-L87)
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L89-L99)
- [benchmark.php](file://tests/benchmark.php#L15-L21)

### Schema Compilation and Optimization
- ValidatorCompiler.compile(key, rules, compiler):
  - Builds CompiledSchema via compiler callback.
  - Stores in object cache (if configured).
  - Writes native PHP code to cachePath/native/<key>.php.
  - Supports legacy precompiled artifacts.
- NativeCompiler.compile(schema):
  - Generates optimized PHP that inlines common rules, handles bail and nullable early exits, and supports dot notation via DataHelper for deeper nesting.
  - Uses a unique key derived from rules plus environment identifiers.

```mermaid
sequenceDiagram
participant App as "Application"
participant VC as "ValidatorCompiler"
participant CS as "CompiledSchema"
participant NC as "NativeCompiler"
participant FS as "Filesystem"
App->>VC : "compile(key, rules, compiler)"
VC->>CS : "compiler(rules)"
VC->>VC : "cache.put(key, CS)"
VC->>NC : "compile(CS)"
NC-->>VC : "optimized PHP code"
VC->>FS : "write native file"
VC-->>App : "CompiledSchema"
```

**Diagram sources**
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L33-L73)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L79-L103)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L24-L51)

**Section sources**
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L33-L73)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L79-L103)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L24-L51)

### Reuse Patterns and Caching
- Use SchemaCacheInterface to persist CompiledSchema across requests.
- ValidatorCompiler.generateKey and NativeCompiler.generateKey ensure cache keys reflect rule sets and environments.
- Precompiled artifacts can be cleared via clearPrecompiled.

**Section sources**
- [SchemaCacheInterface.php](file://src/Cache/SchemaCacheInterface.php#L10-L35)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L190-L193)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L56-L58)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L164-L183)

### Best Practices and Maintainability
- Prefer explicit required vs implicit required variants for clarity.
- Use when(...) to encapsulate complex conditional logic cleanly.
- Keep nested field names shallow; leverage dot notation for readability.
- Group related fields in the same schema; reuse schemas across endpoints.
- Use nullable() for optional fields and avoid mixing required() with nullable() unless intentional.
- Favor bail() to short-circuit expensive validations when a prior rule fails.
- Use presence/filling rules (present(), filled()) to enforce existence semantics separate from required().
- Cache compiled schemas in production to reduce startup overhead.

[No sources needed since this section provides general guidance]

## Dependency Analysis
The following diagram highlights key dependencies among schema components and execution/runtime layers.

```mermaid
graph LR
SB["SchemaBuilder"] --> FD["FieldDefinition"]
FD --> CS["CompiledSchema"]
CS --> CF["CompiledField"]
CS --> VE["ValidatorEngine"]
VE --> CTX["ValidationContext"]
VE --> DH["DataHelper"]
VC["ValidatorCompiler"] --> CS
VC --> NC["NativeCompiler"]
CF --> Rules["RuleInterface implementations"]
```

**Diagram sources**
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php#L9-L35)
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L9-L657)
- [CompiledSchema.php](file://src/Execution/CompiledSchema.php#L9-L67)
- [CompiledField.php](file://src/Execution/CompiledField.php#L10-L176)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L11-L176)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L7-L97)
- [DataHelper.php](file://src/Execution/DataHelper.php#L10-L31)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L10-L194)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L17-L309)

**Section sources**
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php#L9-L35)
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L9-L657)
- [CompiledSchema.php](file://src/Execution/CompiledSchema.php#L9-L67)
- [CompiledField.php](file://src/Execution/CompiledField.php#L10-L176)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L11-L176)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L7-L97)
- [DataHelper.php](file://src/Execution/DataHelper.php#L10-L31)
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L10-L194)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L17-L309)

## Performance Considerations
- CompiledSchema reduces reflection and dynamic dispatch during validation.
- NativeCompiler inlines common rules and uses early exits for bail and nullable, minimizing branches and allocations.
- Dot notation handling uses DataHelper for deeper nesting to keep generated code compact.
- ValidatorEngine short-circuits on fail-fast or max-errors thresholds.
- Cache CompiledSchema and native code to avoid repeated compilation.

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
- Unexpectedly passing empty strings or arrays: Non-implicit rules are skipped when values are considered empty. Use explicit required variants or filled() to enforce presence and non-empty.
- Nested field access issues: Ensure dot notation aligns with actual data shape; CompiledField and DataHelper handle depth-2 and deeper respectively.
- Conditional rules not triggering: Verify the condition evaluates to true/false or returns the expected result when called with ValidationContext.
- Exclusion rules: Fields excluded by exclude()/excludeIf()/etc. are not validated and are reported separately.
- Bail behavior: With bail(), validation stops at the first failing rule for a field; confirm ordering if relying on short-circuiting.

**Section sources**
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L78-L94)
- [CompiledField.php](file://src/Execution/CompiledField.php#L148-L161)
- [NativeCompiler.php](file://src/Compilation/NativeCompiler.php#L102-L109)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L43-L73)

## Conclusion
vi/validation’s schema system offers a powerful, fluent DSL for building maintainable validation logic. Its combination of a builder, compiled representation, and native code generation yields high performance while preserving flexibility through dot notation, conditional rules, and presence/missing semantics. Proper use of caching, bail, and conditional composition leads to robust, efficient validation suites suitable for production systems.

[No sources needed since this section summarizes without analyzing specific files]

## Appendices

### Appendix A: Example Workflows

#### Basic Schema Construction
- Chain field definitions and compile to CompiledSchema.
- Validate a dataset and inspect ValidationResult.

References:
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L14-L18)
- [CompiledSchema.php](file://src/Execution/CompiledSchema.php#L59-L66)

#### Nested Schema Construction
- Use dot notation in field names to model nested structures.
- Validate nested data and assert success/failure.

References:
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L53-L56)
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L68-L87)
- [benchmark.php](file://tests/benchmark.php#L19-L20)

#### Conditional Validation Composition
- Compose conditional blocks using when(condition, onTrue, onFalse?).
- Use callables receiving ValidationContext for dynamic conditions.

References:
- [FieldDefinition.php](file://src/Schema/FieldDefinition.php#L604-L622)
- [ConditionalRule.php](file://src/Rules/ConditionalRule.php#L36-L52)
- [ValidationContext.php](file://src/Execution/ValidationContext.php#L37-L41)

### Appendix B: Schema Versioning and Inheritance Patterns
- Versioning: Include a version identifier in the cache key (e.g., ValidatorCompiler.generateKey) to invalidate caches when schema rules change.
- Inheritance: Compose reusable FieldDefinition fragments by building partial schemas and merging rules arrays; use setRulesArray on SchemaBuilder to share metadata across versions.

References:
- [ValidatorCompiler.php](file://src/Compilation/ValidatorCompiler.php#L190-L193)
- [SchemaBuilder.php](file://src/Schema/SchemaBuilder.php#L26-L29)