# CLAUDE.md — working on the Mondoc library

Guidance for AI agents making changes **to this repository**. If you are instead trying to *use*
Mondoc from an application, read [`docs/agent-reference.md`](./docs/agent-reference.md) — it is the
complete, verified API reference.

## What this repo is

`district5/mondoc` — a model + service abstraction layer over the official `mongodb/mongodb` PHP
driver. It is an ODM-lite: no annotations, no proxies, no unit-of-work, no code generation. A
document maps to a **model** (typed properties), and all querying lives in a **service** (static
methods on a class that knows its collection name).

- PSR-4 root: `District5\Mondoc\` → `src/`
- Tests: `District5Tests\MondocTests\` → `tests/MondocTests/`
- PHP `>= 8.2`, requires `ext-mongodb` and the MongoDB PHP library v2.x (since Mondoc 7.0.0)
- Public docs site: <https://mondoc.district5.dev> (the source of truth for user-facing prose)
- Versioning is SemVer; current line is `7.x` (latest tag `7.6.1`)

### Sibling District5 packages this depends on

| Package                       | Namespace                     | Role                                                                             |
|-------------------------------|-------------------------------|----------------------------------------------------------------------------------|
| `district5/mondoc-builder`    | `District5\MondocBuilder\`    | Fluent query builder (`QueryBuilder`, `QueryTypes\*`)                            |
| `district5/mondoc-encryption` | `District5\MondocEncryption\` | Field-level encryption adapters (Sodium AES256 / SecretBox / Null)               |
| `district5/date`              | `District5\Date\`             | Date creation/manipulation (`Date::nowUtc()`, `Date::mongo()`, `Date::modify()`) |

Do not reimplement date maths or query building in this repo — delegate to those packages. Commit
`2f30aff` deliberately removed Mondoc's own date-conversion complexity in favour of `district5/date`.

## Architecture

Everything is composed from small single-responsibility traits. When adding a capability, add a
trait and `use` it in the relevant aggregate class — do not grow the base classes.

```
src/
├── MondocConfig.php                 Singleton: databases, service map, encryption adapter
├── Traits/Config/                   The three concerns of MondocConfig, one trait each
├── Db/
│   ├── Model/
│   │   ├── MondocAbstractSubModel   Base for nested/embedded models (no _id, no persistence)
│   │   ├── MondocAbstractModel      Extends the above; adds _id, save/delete, hooks, dirty tracking
│   │   └── Traits/
│   │       ├── Mondoc*Trait         OPT-IN traits a user adds to their model (dates, versioning,
│   │       │                        revision numbers, cloning, retention)
│   │       └── Static/              ALWAYS-ON internals mixed into the base models (object id,
│   │                                aliases, nesting, encryption, dirty, bson, exclusions)
│   └── Service/
│       ├── MondocAbstractService    Composes every service trait; abstract getCollectionName()
│       ├── ServiceSub/AggregateSubService   Returned by Service::aggregate()
│       └── Traits/                  One trait per operation family (Get*, Persistence, Deletion,
│                                    Pagination, Countable, Existence, Atomic, Operators, Distinct,
│                                    FieldOperations, Aggregation/*)
├── Extensions/
│   ├── Retention/                   Change-history extension (model + service, own collection)
│   └── FieldEncryption/             MondocEncryptionInterface (implemented by mondoc-encryption)
├── Helper/                          MondocTypes, MondocPaginationHelper, FilterFormatter, HasTraitHelper
├── Dto/                             Plain read-only DTOs returned by financial aggregations
└── Exception/                       MondocException + two subclasses
```

### The two directories under `Db/Model/Traits/` mean different things

- `Db/Model/Traits/*` — **user-facing, opt-in.** A consumer writes `use MondocCreatedDateTrait;` in
  their model. Each adds persisted properties and public accessors. Adding one here is a public API
  addition.
- `Db/Model/Traits/Static/*` — **internal machinery**, already mixed into the abstract models. A
  consumer never `use`s these directly. Note the folder name is historical; these are not static
  methods.

### Key invariants — do not break these

1. **`getPropertyExclusions()` is the persistence boundary.** Any new internal property added to a
   model trait *must* be added to `ExcludedPropertiesTrait::getPropertyExclusions()`, or it will be
   written to MongoDB. `_mondoc`-prefixed naming is the convention.
2. **Private properties are never persisted.** `getMondocObjectVars()` calls `get_object_vars($this)`
   from `MondocAbstractSubModel` scope, so subclass-private properties are invisible to it. This is
   relied upon (e.g. `MyModel::$_controlAsArray` in tests). Protected and public properties *are*
   persisted.
3. **Every filter and update array passed to the driver goes through `FilterFormatter::format()`.**
   That is what recursively converts `DateTime` → `UTCDateTime`, and it is why users can put PHP
   dates straight into a filter. New query methods must call it (or delegate to one that does).
4. **Field aliases have two directions and they are easy to confuse.**
   `getFieldAliasMapRemoteName()` → DB key; `getFieldAliasMapLocalName()` → PHP property. Data
   leaving the model is remote-named, data entering is local-named. `getDirty()` returns *local*
   names; `UpdateSingleTrait::update()` maps them to remote names when building `$set`.
5. **Services are entirely static.** State lives on models and in `MondocConfig`. Instance methods
   only exist on `AggregateSubService` (which holds the service FQCN it was constructed with).
6. **Model → service resolution is `MondocConfig::getServiceForModel()`,** and back again via
   `getModelForService()`. `MondocRetentionModel => MondocRetentionService` is injected implicitly by
   `getExtensionsServiceMap()`, so users never register it. Multi-service operations
   (`insertMulti`, `updateManyModels`, `deleteModels`) use this to route models to the right
   collection, so any new bulk method should do the same.

## Testing

Tests hit a **real MongoDB**. There are no mocks or in-memory doubles.

```sh
composer install
export MONGO_CONNECTION_STRING='mongodb://localhost:27017'   # required, no default
php vendor/bin/phpunit                    # whole suite
php vendor/bin/phpunit --testdox
php vendor/bin/phpunit --coverage-text
php vendor/bin/phpunit --filter LifecycleHooksTest
```

- `MONGO_DATABASE` is set to `MondocTests_` by `phpunit.xml`; `tests/bootstrap.php` appends
  `php_uname('s')` (so `MondocTests_Darwin` locally, `MondocTests_Linux` in CI).
- `tests/bootstrap.php` registers every database/service mapping and **drops all test collections on
  start**. Collection names come from `AbstractTestService::getCollectionName()` →
  `'test_' . <short service class name>`.
- `phpunit.xml` sets `stopOnError`/`stopOnFailure` — the run aborts on the first problem.
- CI (`.github/workflows/ci.yml`) runs on every push against PHP 8.4 with a `mongodb-group`
  concurrency lock (the shared test database cannot take parallel runs), and uploads coverage to
  Codecov.

### Adding a test

1. Extend `District5Tests\MondocTests\MondocBaseTestAbstract`, not `TestCase` directly.
2. Put fixtures in `tests/MondocTests/TestObjects/Model/` and `.../Service/`, extending
   `AbstractTestService` so the collection name is derived automatically.
3. **Register the model → service pair in `tests/bootstrap.php`** or every call will throw
   `MondocServiceMapErrorException`. This is the single most common reason a new test fails.
4. Use `$this->getUniqueKey()` for values that must not collide between tests.
5. Clean up documents your test inserted; the bootstrap only cleans at start-of-run.

## Conventions to follow when editing

- Every file starts with the District5 MIT licence header block. Copy it verbatim into new files.
- Full PHPDoc on every method: `@param`, `@return`, and **every** `@throws`. Commit `74fab4b` exists
  purely to fix missing PHPDoc, and `d9a2172` was a code-sniff pass — reviewers care about this.
- `@package` matches the namespace. Class docblocks read `Class X.` / `Trait X.` / `Interface X.`.
- Comparisons are explicit and strict: `if ($x === false)`, `if (null === $y)`, `in_array($v, $a, true)`
  where identity matters.
- Fluent setters return `static` and are documented `@return $this`.
- Methods that operate on a collection link the relevant driver page with `@see https://www.mongodb.com/docs/php-library/...`.
- Non-obvious behaviour goes in an `@example` block (see `IncrementTrait::incMulti`).
- Where a variable is typed as a service/model FQCN string but annotated as an object for IDE
  benefit, keep the existing `/* @var $x Foo - it's not. It's actually a string. */` idiom.

## Known documentation drift (verify before repeating)

`README.md` has fallen behind the code in three places. Correct behaviour is in
`docs/agent-reference.md`; fix the README rather than propagating these:

| README says                                                                                         | Reality                                                                                                                                                                      |
|-----------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$model->setMondocRetentionData([...])`                                                             | Method is `setMondocRetentionChangeMeta()` (`MondocRetentionTrait`)                                                                                                          |
| `getPageByByObjectIdPagination($paginator, $currentId, $perPage, $sortDirection)`                   | Signature is `($paginator, $currentId, $sortDirection = -1)` — three arguments; `perPage` comes from the paginator                                                           |
| `new \District5\Mondoc\Db\Builder\MondocBuilder\MondocBuilder()` then `->addFilter('name', 'John')` | Class is `District5\MondocBuilder\QueryBuilder`; there is no `addFilter()`. Build a `ValueEqualTo`, call `->string('n', 'John')` on it, then `$builder->addQueryPart($part)` |

## Do not

- Commit unless explicitly asked.
- Add a dependency without checking whether `district5/date`, `mondoc-builder` or `mondoc-encryption`
  already covers it.
- Introduce non-static service methods, or move query logic into models — the model/service split is
  the library's whole premise.
- Worry about `coverage.xml`, `junit.xml`, `build/`, `vendor/` or `composer.lock` — all untracked and
  gitignored. Leave them out of any change you describe.
