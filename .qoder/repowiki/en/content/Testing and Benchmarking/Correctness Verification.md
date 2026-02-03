# Correctness Verification

<cite>
**Referenced Files in This Document**
- [fuzz_mismatches.php](file://tests/fuzz_mismatches.php)
- [check_parity.php](file://tests/check_parity.php)
- [correctness_verify.php](file://tests/correctness_verify.php)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php)
- [ValidationResult.php](file://src/Execution/ValidationResult.php)
- [ErrorCollector.php](file://src/Execution/ErrorCollector.php)
- [FastValidatorFactory.php](file://src/Laravel/FastValidatorFactory.php)
- [LaravelValidatorAdapter.php](file://src/Laravel/LaravelValidatorAdapter.php)
- [FastValidator.php](file://src/Laravel/Facades/FastValidator.php)
- [RuleRegistry.php](file://src/Rules/RuleRegistry.php)
- [RuleInterface.php](file://src/Rules/RuleInterface.php)
- [Validator.php](file://src/Validator.php)
- [composer.json](file://composer.json)
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php)
- [RuleIdTest.php](file://tests/Unit/Rules/RuleIdTest.php)
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
This document describes correctness verification and validation accuracy testing workflows for the vi/validation library. It focuses on parity checking against Laravel’s built-in validator, fuzz testing strategies to uncover edge-case discrepancies, and automated validation accuracy checks. It also covers test data generation, rule implementation validation, backward compatibility, and precision across data types and scenarios.

## Project Structure
The repository is organized around a high-performance validation engine with Laravel integration points and a comprehensive suite of unit and correctness tests. Key areas:
- Core engine: execution, schema compilation, and rule registry
- Laravel integration: factory, adapter, and facade
- Tests: parity checks, fuzzing, correctness verification, and unit tests

```mermaid
graph TB
subgraph "Core Engine"
VE["ValidatorEngine"]
VR["ValidationResult"]
EC["ErrorCollector"]
SC["SchemaValidator"]
end
subgraph "Rules"
RR["RuleRegistry"]
RI["RuleInterface"]
end
subgraph "Laravel Integration"
FVF["FastValidatorFactory"]
LVA["LaravelValidatorAdapter"]
FV["FastValidator (Facade)"]
end
subgraph "Tests"
CP["check_parity.php"]
FM["fuzz_mismatches.php"]
CV["correctness_verify.php"]
UT["ValidatorTest.php"]
end
CP --> FVF
FM --> FVF
CV --> FV
FVF --> VE
VE --> EC
VE --> VR
FVF --> RR
RR --> RI
LVA --> FVF
FV --> FVF
UT --> SC
```

**Diagram sources**
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L1-L177)
- [ValidationResult.php](file://src/Execution/ValidationResult.php#L1-L142)
- [ErrorCollector.php](file://src/Execution/ErrorCollector.php#L1-L51)
- [FastValidatorFactory.php](file://src/Laravel/FastValidatorFactory.php#L1-L207)
- [LaravelValidatorAdapter.php](file://src/Laravel/LaravelValidatorAdapter.php#L1-L56)
- [FastValidator.php](file://src/Laravel/Facades/FastValidator.php#L1-L23)
- [RuleRegistry.php](file://src/Rules/RuleRegistry.php#L1-L302)
- [RuleInterface.php](file://src/Rules/RuleInterface.php#L1-L16)
- [check_parity.php](file://tests/check_parity.php#L1-L73)
- [fuzz_mismatches.php](file://tests/fuzz_mismatches.php#L1-L75)
- [correctness_verify.php](file://tests/correctness_verify.php#L1-L71)
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L1-L123)

**Section sources**
- [composer.json](file://composer.json#L1-L36)

## Core Components
- ValidatorEngine orchestrates validation execution, applies rules, manages bail and fail-fast behavior, and collects errors.
- ValidationResult exposes standardized accessors for validity, raw errors, formatted messages, and first messages.
- ErrorCollector accumulates rule failures with field, rule name, parameters, and optional custom messages.
- FastValidatorFactory builds SchemaValidator instances from Laravel-style rules, supports caching, and configures message resolution and performance options.
- RuleRegistry registers built-in rules and resolves rule names/aliases to concrete implementations.
- LaravelValidatorAdapter integrates the fast engine into Laravel’s Validator factory interface.
- Facade FastValidator provides a convenient entry point mirroring Laravel’s API.

**Section sources**
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L1-L177)
- [ValidationResult.php](file://src/Execution/ValidationResult.php#L1-L142)
- [ErrorCollector.php](file://src/Execution/ErrorCollector.php#L1-L51)
- [FastValidatorFactory.php](file://src/Laravel/FastValidatorFactory.php#L1-L207)
- [RuleRegistry.php](file://src/Rules/RuleRegistry.php#L1-L302)
- [LaravelValidatorAdapter.php](file://src/Laravel/LaravelValidatorAdapter.php#L1-L56)
- [FastValidator.php](file://src/Laravel/Facades/FastValidator.php#L1-L23)

## Architecture Overview
The correctness verification pipeline compares the fast engine’s outcomes against Laravel’s Validator for identical inputs and rule sets. The flow ensures parity in pass/fail decisions and error message semantics.

```mermaid
sequenceDiagram
participant Test as "test script"
participant LV as "Laravel Validator"
participant FVF as "FastValidatorFactory"
participant FVV as "FastValidatorWrapper"
participant VE as "ValidatorEngine"
participant SC as "SchemaValidator"
Test->>LV : "make(data, rules)"
LV-->>Test : "Validator instance"
Test->>FVF : "make(data, rules)"
FVF-->>Test : "FastValidatorWrapper"
Test->>LV : "passes()"
Test->>FVV : "passes()"
FVV->>SC : "validate(data)"
SC->>VE : "validate(schema, data)"
VE-->>SC : "ValidationResult"
SC-->>FVV : "ValidationResult"
Test->>Test : "compare passes() and errors()"
```

**Diagram sources**
- [check_parity.php](file://tests/check_parity.php#L55-L63)
- [fuzz_mismatches.php](file://tests/fuzz_mismatches.php#L59-L60)
- [FastValidatorFactory.php](file://src/Laravel/FastValidatorFactory.php#L51-L60)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L33-L98)
- [ValidationResult.php](file://src/Execution/ValidationResult.php#L59-L70)

## Detailed Component Analysis

### Parity Checking Mechanisms
Parity checks compare:
- Pass/fail outcome
- Presence and shape of errors per field
- Formatted messages resolution

Scripts demonstrate:
- Iterating representative data samples and asserting equal outcomes.
- Verifying lazy message resolution and absence of unresolved keys.

```mermaid
flowchart TD
Start(["Start Parity Check"]) --> Prep["Prepare Laravel and Fast factories"]
Prep --> LoadRules["Load shared rules and data"]
LoadRules --> LVMake["Create Laravel Validator"]
LoadRules --> FVMk["Create Fast Validator"]
LVMake --> LVRes["Run passes() and collect errors"]
FVMk --> FVRes["Run passes() and collect errors"]
LVRes --> Compare{"Outcomes Equal?"}
Compare --> |No| Report["Report mismatch details"]
Compare --> |Yes| Next["Next item"]
Report --> Next
Next --> End(["End"])
```

**Diagram sources**
- [check_parity.php](file://tests/check_parity.php#L52-L72)
- [correctness_verify.php](file://tests/correctness_verify.php#L41-L70)

**Section sources**
- [check_parity.php](file://tests/check_parity.php#L1-L73)
- [correctness_verify.php](file://tests/correctness_verify.php#L1-L71)

### Fuzz Testing Strategies
Fuzzing generates random data items and compares outcomes across a thousand iterations. It surfaces discrepancies in:
- Mixed data types (strings, integers, nulls)
- Edge cases (empty strings, invalid emails, boundary values)
- Optional fields presence/absence

```mermaid
flowchart TD
Seed(["Seed Random Generator"]) --> Loop["Repeat 1000 times"]
Loop --> Gen["Generate random item"]
Gen --> LV["Laravel Validator"]
Gen --> FV["Fast Validator"]
LV --> LVRes["passes() and errors()"]
FV --> FVRes["passes() and errors()"]
LVRes --> Match{"Equal outcomes?"}
FVRes --> Match
Match --> |No| Record["Record discrepancy"]
Match --> |Yes| Next["Next iteration"]
Record --> Limit{"Reached limit?"}
Limit --> |No| Loop
Limit --> |Yes| Stop(["Stop"])
Next --> Loop
```

**Diagram sources**
- [fuzz_mismatches.php](file://tests/fuzz_mismatches.php#L56-L70)

**Section sources**
- [fuzz_mismatches.php](file://tests/fuzz_mismatches.php#L1-L75)

### Correctness Verification Workflows
Workflows include:
- Lazy message resolution verification: ensure messages are resolved and not left as unresolved keys.
- Backward compatibility checks: confirm RuleId enum coverage matches registered rules.
- Precision validation: nested fields, nullable fields, batch validation.

```mermaid
sequenceDiagram
participant Test as "correctness_verify.php"
participant Facade as "FastValidator (Facade)"
participant FVF as "FastValidatorFactory"
participant SC as "SchemaValidator"
participant VE as "ValidatorEngine"
Test->>Facade : "make(data, rules)"
Facade->>FVF : "make(...)"
FVF-->>Test : "FastValidatorWrapper"
Test->>SC : "validate(data)"
SC->>VE : "validate(schema, data)"
VE-->>SC : "ValidationResult"
SC-->>Test : "ValidationResult"
Test->>Test : "Assert messages resolved and errors present"
```

**Diagram sources**
- [correctness_verify.php](file://tests/correctness_verify.php#L41-L70)
- [FastValidator.php](file://src/Laravel/Facades/FastValidator.php#L16-L22)
- [FastValidatorFactory.php](file://src/Laravel/FastValidatorFactory.php#L51-L60)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L33-L98)

**Section sources**
- [correctness_verify.php](file://tests/correctness_verify.php#L1-L71)
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L1-L123)
- [RuleIdTest.php](file://tests/Unit/Rules/RuleIdTest.php#L1-L73)

### Automated Validation Accuracy Checks
Automated checks leverage:
- PHPUnit tests for schema-level validation (pass/fail, nested fields, nullable, batch).
- Rule registry coverage assertions to maintain backward compatibility.
- Consistent message resolution via MessageResolver in ValidationResult.

```mermaid
classDiagram
class ValidatorEngine {
+validate(schema, data) ValidationResult
+setFailFast(failFast) void
+setMaxErrors(maxErrors) void
}
class ValidationResult {
+isValid() bool
+errors() array
+messages() array
+firstMessage(field) string?
}
class ErrorCollector {
+add(field, rule, message?, params=[]) void
+all() array
}
class FastValidatorFactory {
+make(data, rules, messages=[], attributes=[]) FastValidatorWrapper
}
class RuleRegistry {
+registerBuiltInRules() void
+resolve(name) RuleInterface
}
ValidatorEngine --> ErrorCollector : "collects errors"
ValidatorEngine --> ValidationResult : "produces"
FastValidatorFactory --> ValidatorEngine : "configures"
RuleRegistry --> RuleInterface : "resolves"
```

**Diagram sources**
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L1-L177)
- [ValidationResult.php](file://src/Execution/ValidationResult.php#L1-L142)
- [ErrorCollector.php](file://src/Execution/ErrorCollector.php#L1-L51)
- [FastValidatorFactory.php](file://src/Laravel/FastValidatorFactory.php#L1-L207)
- [RuleRegistry.php](file://src/Rules/RuleRegistry.php#L1-L302)

**Section sources**
- [ValidatorTest.php](file://tests/Unit/ValidatorTest.php#L1-L123)
- [RuleIdTest.php](file://tests/Unit/Rules/RuleIdTest.php#L1-L73)

### Ensuring Backward Compatibility
Backward compatibility is ensured by:
- Registering all built-in rules in RuleRegistry.
- Confirming that every RuleId enum value has a corresponding registered rule.
- Verifying that registered rules have matching enum cases.

```mermaid
flowchart TD
LoadRules["Load built-in rules"] --> EnumCases["Enumerate RuleId cases"]
EnumCases --> CheckReg["Check registry.has(value)"]
CheckReg --> |Missing| Fail["Fail test"]
CheckReg --> |Present| Next["Next case"]
Next --> Done(["All covered"])
```

**Diagram sources**
- [RuleIdTest.php](file://tests/Unit/Rules/RuleIdTest.php#L26-L31)
- [RuleRegistry.php](file://src/Rules/RuleRegistry.php#L195-L300)

**Section sources**
- [RuleIdTest.php](file://tests/Unit/Rules/RuleIdTest.php#L1-L73)
- [RuleRegistry.php](file://src/Rules/RuleRegistry.php#L1-L302)

### Maintaining Validation Precision Across Data Types
Precision is maintained by:
- Explicit handling of empty values and implicit vs non-implicit rules.
- Bail behavior to stop field validation on first failure.
- Fail-fast and max-errors thresholds to control resource usage.
- Nullable fields allowing null without triggering non-implicit rules.

```mermaid
flowchart TD
Enter(["Field Validation"]) --> EmptyCheck{"Value empty<br/>and rule non-implicit?"}
EmptyCheck --> |Yes| Skip["Skip rule"]
EmptyCheck --> |No| Apply["Apply rule"]
Apply --> Error{"Rule fails?"}
Error --> |No| Continue["Continue to next rule"]
Error --> |Yes| AddErr["Add error"]
AddErr --> Bail{"Field has bail?"}
Continue --> Bail
Bail --> |Yes| StopField["Stop field validation"]
Bail --> |No| NextRule["Next rule"]
NextRule --> MaxErr{"Reached max errors?"}
MaxErr --> |Yes| StopAll["Stop validation"]
MaxErr --> |No| Apply
```

**Diagram sources**
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L76-L95)

**Section sources**
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L1-L177)

## Dependency Analysis
External dependencies and integration points:
- illuminate/validation is required for Laravel integration and Validator compatibility.
- Composer extra laravel provider and alias enable seamless Laravel usage.

```mermaid
graph LR
App["Application"] --> FSP["FastValidationServiceProvider"]
FSP --> FVF["FastValidatorFactory"]
FVF --> VE["ValidatorEngine"]
VE --> RR["RuleRegistry"]
FVF --> SC["SchemaValidator"]
App --> LV["Illuminate\\Validation\\Factory"]
```

**Diagram sources**
- [composer.json](file://composer.json#L23-L31)
- [FastValidatorFactory.php](file://src/Laravel/FastValidatorFactory.php#L1-L207)
- [LaravelValidatorAdapter.php](file://src/Laravel/LaravelValidatorAdapter.php#L1-L56)

**Section sources**
- [composer.json](file://composer.json#L1-L36)

## Performance Considerations
- Fail-fast and max-errors tuning controls throughput and memory usage during validation.
- Schema caching reduces repeated parsing and compilation overhead.
- Lazy message resolution defers translation lookups until messages are requested.

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
Common issues and remedies:
- Unresolved messages: ensure message resolver is configured and messages are not returned as unresolved keys.
- Mismatches between engines: verify identical rule definitions, locales, and custom attributes/messages.
- Excessive errors: adjust max-errors or enable fail-fast to cut off long-running validations.
- Rule registration conflicts: confirm unique rule names and aliases in RuleRegistry.

**Section sources**
- [correctness_verify.php](file://tests/correctness_verify.php#L54-L63)
- [ValidationResult.php](file://src/Execution/ValidationResult.php#L77-L96)
- [ValidatorEngine.php](file://src/Execution/ValidatorEngine.php#L148-L159)
- [RuleRegistry.php](file://src/Rules/RuleRegistry.php#L55-L78)

## Conclusion
The correctness verification framework combines parity checks, fuzz testing, and automated unit tests to ensure the fast engine behaves identically to Laravel’s validator. By registering all built-in rules, resolving messages lazily, and controlling validation behavior via configurable options, the system maintains backward compatibility and precision across diverse data types and scenarios.

[No sources needed since this section summarizes without analyzing specific files]

## Appendices
- Example scripts for parity, fuzzing, and correctness verification are located under tests/.
- Rule implementations are registered centrally via RuleRegistry and resolved through RuleInterface.

[No sources needed since this section provides general guidance]