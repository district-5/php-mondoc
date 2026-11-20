# Mondoc — agent reference

A dense, verified reference for AI agents writing application code with `district5/mondoc`.
Everything here was read out of `src/` at version `7.6.1`; where the README or the docs site
disagrees, the [Corrections](#corrections-to-published-docs) section says so.

Human-readable prose docs: <https://mondoc.district5.dev>. Use this file for the exact API surface,
signatures and behaviour, and that site for tutorials.

---

## 1. Mental model (read this first)

Mondoc is an **ODM-lite** over the official MongoDB PHP library. Three things exist:

| Thing         | Base class               | Responsibility                                                                      |
|---------------|--------------------------|-------------------------------------------------------------------------------------|
| **Model**     | `MondocAbstractModel`    | Typed properties = document fields. Holds `_id`. Knows how to save/delete *itself*. |
| **Sub-model** | `MondocAbstractSubModel` | An embedded document. No `_id`, cannot be saved on its own.                         |
| **Service**   | `MondocAbstractService`  | All-static. Knows the collection name. Every query lives here.                      |

They are wired together by a **singleton `MondocConfig`** holding (a) MongoDB databases keyed by a
connection id, (b) a model-FQCN → service-FQCN map, and (c) an optional field-encryption adapter.

What Mondoc deliberately does **not** have: annotations/attributes, proxies, lazy loading, a
unit of work, entity manager, migrations, or relations/joins. There is no `find()` on the model.
Queries return fully-inflated model instances; writes are dirty-tracked field-level `$set`s.

```php
$user = UserService::getById($id);       // service reads  → model
$user->setName('Alice');                 // plain PHP setter
$user->save();                            // model writes via its mapped service
```

**Non-negotiable:** a model whose class is not in the service map throws
`MondocServiceMapErrorException` the moment you call `save()`, `delete()`, `inc()`, or any service
read that has to inflate it. Register every pair at bootstrap.

---

## 2. Install and requirements

```sh
composer require district5/mondoc
```

- PHP `~8.2.35 || ~8.3.36 || ~8.4.27 || >=8.5.12` (the PHP2Shell-patched releases; tested on 8.2–8.6), `ext-mongodb`, `mongodb/mongodb` **v2.x** (required since Mondoc 7.0.0)
- Pulls in `district5/date` (`>= 3.0.4`), `district5/mondoc-builder` (`>= 2.4.0`), `district5/mondoc-encryption`
- `ext-sodium` suggested — needed only if you use field encryption

---

## 3. Bootstrap

```php
use District5\Mondoc\MondocConfig;
use MongoDB\Client;

$client = new Client('mongodb://localhost:27017');

$config = MondocConfig::getInstance();

// (a) Databases, keyed by connection id. 'default' is the fallback used by every service
//     that does not override getConnectionId().
$config->addDatabase($client->selectDatabase('app'));                    // 'default'
$config->addDatabase($client->selectDatabase('auth'), 'authentication'); // named

// (b) Model → Service map. Either wholesale...
$config->setServiceMap([
    UserModel::class  => UserService::class,
    OrderModel::class => OrderService::class,
]);
// ...or incrementally (chainable):
$config->addServiceMapping(InvoiceModel::class, InvoiceService::class);

// (c) Optional: field-level encryption
$config->setEncryptionAdapter(
    new \District5\MondocEncryption\Adapters\Sodium\AES256Adapter($base64Key)
);
```

### `MondocConfig` API

| Method                                                                                                                                                          | Notes                                                             |
|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------|
| `getInstance(): MondocConfig`                                                                                                                                   | Singleton accessor                                                |
| `reconstruct(): MondocConfig`                                                                                                                                   | Throws away the instance and returns a fresh one. Test/reset only |
| `addDatabase(Database $db, string $connectionId = 'default'): static`                                                                                           |                                                                   |
| `getDatabase(string $connectionId = 'default'): Database`                                                                                                       | Throws `MondocConfigConfigurationException` if unknown            |
| `getDatabaseName(string $connectionId = 'default'): string`                                                                                                     |                                                                   |
| `hasConnectionId(string $connectionId): bool`                                                                                                                   |                                                                   |
| `getCollection(string $name, string $connectionId = 'default'): Collection`                                                                                     |                                                                   |
| `setServiceMap(array $map): static`                                                                                                                             | **Replaces** the map                                              |
| `addServiceMapping(string $modelFQCN, string $serviceFQCN): static`                                                                                             |                                                                   |
| `getServiceMap(): array`                                                                                                                                        | Does *not* include the implicit extension mappings                |
| `getServiceForModel(string $modelFQCN): string`                                                                                                                 | Throws `MondocServiceMapErrorException`                           |
| `getModelForService(string $serviceFQCN): ?string`                                                                                                              | Reverse lookup, memoised; throws if unmapped                      |
| `setEncryptionAdapter(?MondocEncryptionInterface $a): static` / `getEncryptionAdapter()` / `hasEncryptionAdapter(): bool` / `removeEncryptionAdapter(): static` |                                                                   |

`MondocRetentionModel => MondocRetentionService` is merged in automatically by
`getExtensionsServiceMap()` — never register it yourself.

---

## 4. Models

```php
use District5\Mondoc\Db\Model\MondocAbstractModel;

class UserModel extends MondocAbstractModel
{
    protected string $name = '';
    protected int $age = 0;
    protected ?DateTime $lastLogin = null;

    public function getName(): string { return $this->name; }
    public function setName(string $v): static { $this->name = trim($v); return $this; }
    // ...
}
```

### 4.1 What gets persisted

| Visibility  | Persisted?                                     |
|-------------|------------------------------------------------|
| `public`    | **yes**                                        |
| `protected` | **yes**                                        |
| `private`   | **no** — use this for transient/computed state |

Plus these are always excluded (`ExcludedPropertiesTrait::getPropertyExclusions()`):
`_mondocObjectId`, `_mondocBson`, `_mondocCollection`, `_mondocPresetObjectId`, `_mondocUnmapped`,
`_mondocDirty`, `_mondocEstablishedNestedSingle`, `_mondocEstablishedNestedMultiple`,
`_mondocRetentionExpiry`, `_mondocRetentionChangeMeta`, `mondocEncrypted`, `mondocNested`,
`mondocFieldAliases`.

Because ordinary protected properties are auto-persisted, **adding a property to a model is
sufficient to add a field to the document** — no mapping declaration needed.

### 4.2 Identity

`MondocObjectIdTrait` / `PresetObjectIdTrait`:

| Method                                                                                         | Notes                                           |
|------------------------------------------------------------------------------------------------|-------------------------------------------------|
| `getObjectId(): ?ObjectId`                                                                     | `null` until first insert                       |
| `getObjectIdString(): ?string`                                                                 |                                                 |
| `hasObjectId(): bool`                                                                          | The test for "has this been persisted?"         |
| `setObjectId(ObjectId $id): static`                                                            | `final`; called by the service                  |
| `unsetObjectId(): static`                                                                      | Used when cloning                               |
| `setPresetObjectId(?ObjectId $id): static`                                                     | Force the `_id` for the next insert             |
| `hasPresetObjectId(): bool` / `getPresetObjectId(): ?ObjectId` / `clearPresetObjectId(): void` | Cleared automatically after a successful insert |

### 4.3 Persistence from the model

```php
$model->save(array $insertOrUpdateOptions = []): bool   // insert if no _id, else update
$model->delete(): bool                                   // false if never persisted
```

Both delegate to the mapped service (`saveModel()` / `deleteModel()`).
`Model::getMondocServiceClass(): string` returns that service's FQCN (the inverse of
`Service::getMondocModelClass()`).

### 4.4 Field aliases — short keys in the database

```php
class UserModel extends MondocAbstractModel
{
    protected string $name = '';
    protected ?FoodSubModel $favouriteFood = null;

    protected array $mondocFieldAliases = [
        'n'  => 'name',           // dbKey => phpProperty
        'ff' => 'favouriteFood',
    ];
}
```

- Direction is **`'dbKey' => 'phpPropertyName'`**. Getting it backwards silently does nothing.
- Works at any nesting depth; declare the map on the sub-model too for its own fields.
- `_id => _mondocObjectId` is injected automatically.
- `getFieldAliasMapRemoteName('name')` → `'n'`; `getFieldAliasMapLocalName('n')` → `'name'`. Both are
  identity functions for unmapped names, so they are safe to call blindly.
- **Filters, updates, sorts, projections and query-builder parts all use the DB key.** Mondoc does
  not translate query input. `getMultiByCriteria(['name' => 'Alice'])` matches nothing if `name` is
  aliased to `n`.
- `asArray()` and `asJsonEncodableArray()` emit **DB keys** (`n`, not `name`).

### 4.5 Nested models

Sub-models extend `MondocAbstractSubModel`. Declare them in `$mondocNested`; suffix `[]` for an
array of them.

```php
class PersonModel extends MondocAbstractModel
{
    protected ?CarSubModel $car = null;
    protected array $allFoods = [];   // FoodSubModel[]

    protected array $mondocNested = [
        'car'      => CarSubModel::class,
        'allFoods' => FoodSubModel::class . '[]',
    ];
}
```

- Single-nested properties are **eagerly instantiated in the constructor** (`new CarSubModel()`), and
  `[]`-nested ones initialised to `[]`. So `$person->car` is never null after construction even
  before inflation — do not rely on `null` to mean "absent".
- Since 5.1.0 the property type does **not** need to mention `BSONDocument`/`BSONArray`.
- Alternative to the property: override `protected function getMondocNestedModelMap(): array`. Useful
  when the map is computed. (Used by `MixedEncryptedModel` in the test suite.)
- Nesting is arbitrarily deep; sub-models may themselves declare `$mondocNested`.
- Unrecognised `BSONDocument`/`BSONArray` values (not in `$mondocNested`) are inflated to plain PHP
  arrays.

### 4.6 Opt-in model traits

All under `District5\Mondoc\Db\Model\Traits\`.

| Trait                       | Field | Behaviour                                                                                                                                                                                                           |
|-----------------------------|-------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `MondocCreatedDateTrait`    | `cd`  | `getCreatedDate(bool $asMongo = false)`, `setCreatedDate(DateTime)`, `touchCreatedDate()`. Set to now-UTC on first insert **if still null**.                                                                        |
| `MondocModifiedDateTrait`   | `md`  | Same accessors as above. Set on insert if null, and refreshed on every update **unless you already marked `md` dirty yourself**.                                                                                    |
| `MondocRevisionNumberTrait` | `_rn` | `getRevisionNumber()`, `setRevisionNumber(int)`, `incrementRevisionNumber()`, `isRevisionNumberModel()`. Becomes `1` on insert, auto-increments on every update.                                                    |
| `MondocVersionedModelTrait` | `_v`  | `getModelVersion()`, `setModelVersion(int)`, `isModelVersionX(int)`, `incrementModelVersion()`, `decrementModelVersion()`, `isVersionableModel()`. **Manual** — Mondoc never changes it. Use for schema versioning. |
| `MondocCloneableTrait`      | —     | `clone(bool $save = false, string\|MondocAbstractModel\|null $clz = null)`                                                                                                                                          |
| `MondocRetentionTrait`      | —     | Enables change history; see [§9](#9-data-retention-change-history)                                                                                                                                                  |

`_rn` vs `_v`: revision number is bumped by the library on *every* update regardless of what changed;
model version is yours to bump when the shape of your data changes.

`clone()` details: with no `$clz` it is a **shallow** `clone $this` (nested sub-model instances are
shared by reference — mutate them and both copies change). With `$clz` it round-trips through
`asArray()`/`inflateSingleArray()`, giving a deep copy and allowing a class change. Either way the
`_id`, preset id and dirty state are cleared. `$save = true` saves and returns the new model, or
`null` if the save failed.

### 4.7 Lifecycle hooks

Override on your model. `before*` returning `false` vetoes the operation.

| Hook             | Returns | Fired by                                         |
|------------------|---------|--------------------------------------------------|
| `beforeInsert()` | `bool`  | `insert()`, and per-model inside `insertMulti()` |
| `afterInsert()`  | `void`  | after a successful insert                        |
| `beforeUpdate()` | `bool`  | `update()`                                       |
| `afterUpdate()`  | `void`  | after a successful update                        |
| `beforeDelete()` | `bool`  | `deleteModel()`                                  |
| `afterDelete()`  | `void`  | after a successful delete                        |

Exact firing order for a save of a new model:
`touch cd/md`, `_rn → 1` → `beforeInsert()` → `insertOne` → set `_id`, clear preset id, clear dirty,
store original BSON → retention snapshot → `afterInsert()`.

For an update: bump `md`/`_rn` → **dirty check (returns `true` early and fires nothing if clean)** →
`beforeUpdate()` → `updateOne` with `$set`/`$unset` → clear dirty → retention snapshot →
`afterUpdate()`.

In `insertMulti()`, vetoed models are **silently skipped** — the rest still insert, and a skipped
model simply keeps `hasObjectId() === false`. Check that if you need to know.

Hooks do **not** fire for: `updateOne()`/`updateMany()` (raw filter+update), `deleteOne()`/
`deleteMulti()`/`deleteByIds()`/**`deleteModels()`**, `inc`/`dec`, push/pull, or `removeField()`.
`deleteModels()` batches by id and therefore bypasses `beforeDelete()`/`afterDelete()` — loop and
call `$model->delete()` if you need the hooks.

### 4.8 Dirty tracking

Updates only write changed fields. Dirtiness is computed by comparing the current property values
against the `BSONDocument` the model was inflated from (stored via `setOriginalBsonDocument()`).

| Method                                 | Notes                                                                |
|----------------------------------------|----------------------------------------------------------------------|
| `isDirty(?string $property): bool`     | `null` ⇒ "is anything dirty?"                                        |
| `isDirtyField(string $property): bool` | Takes the **DB (remote) key**                                        |
| `getDirty(): array`                    | Returns **local property names**                                     |
| `clearDirty(): static`                 | Called for you after a successful write                              |
| `addDirty(string $property): static`   | `protected` — expose it from your model if you need to force a write |

Consequences worth knowing:

- A model with no original BSON document (i.e. built with `new`) reports **everything** dirty, which
  is why inserts always write in full.
- `update()` **returns `true` and performs no query** when nothing is dirty. A `true` return does not
  prove a write happened.
- Comparison is via `MondocTypes::typeToJsonFriendly()` on both sides, so `DateTime` vs `UTCDateTime`
  compare equal when they represent the same instant.
- Fields present in the original document but no longer set on the model are collected by
  `getMondocKeysToRemove()` and `$unset` in the same update.

### 4.9 Inflation and export

| Method                                                         | Notes                                                                                                                            |
|----------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------|
| `Model::inflateSingleArray(array $data): static`               | Applies aliases, decryption, `UTCDateTime`→`DateTime`, nested inflation; sets `_id` if present; then calls `assignDefaultVars()` |
| `Model::inflateSingleBsonDocument(BSONDocument $doc): static`  | The above + retains the original BSON for dirty tracking                                                                         |
| `Model::inflateMultipleArrays(array $arrayOfArrays): static[]` |                                                                                                                                  |
| `$model->asArray(bool $withEncryption = false): array`         | MongoDB-shaped array (BSON types, DB keys). `true` applies encryption — used internally on write                                 |
| `$model->asJsonEncodableArray(array $omitKeys = []): array`    | JSON-safe (`ObjectId`→string, dates→`Uv` millisecond strings, enums→`->value`). On a top-level model, adds `_id` as a string     |

Note the JSON date format: `format('Uv')` = Unix seconds followed by milliseconds, as a **string**
(e.g. `"1712664000123"`), not ISO-8601.

`protected function assignDefaultVars(): void` runs **after** inflation as well as (in some models)
before export — always test for presence before assigning, or you will clobber loaded values:

```php
protected function assignDefaultVars(): void
{
    if ($this->version < 1) { $this->version = 1; }
}
```

### 4.10 Unmapped properties

Document keys with no matching property are captured in `_mondocUnmapped` and are readable/writable
through `__get()`/`__set()`. They round-trip back to the database on save (merged in `asArray()`).

`getUnmappedFields(): array`, `hasUnmappedProperties(): bool`. `Service::removeField($field, $model)`
deletes such a key — and **only works for unmapped fields** (it returns `false` for a real property).

### 4.11 Field-level encryption

```php
class PatientModel extends MondocAbstractModel
{
    protected string $name = '';
    protected string $ssn = '';
    protected ?DateTime $dateOfBirth = null;

    protected array $mondocEncrypted = ['ssn', 'dateOfBirth'];   // LOCAL property names
}
```

- Requires an adapter on `MondocConfig`. **With no adapter registered, encryption is a silent no-op** —
  values are stored in plaintext, no exception.
- Adapters ship in `district5/mondoc-encryption`:
  `District5\MondocEncryption\Adapters\Sodium\{AES256Adapter, SecretBoxAdapter}` and `Adapters\NullAdapter`.
  `AES256Adapter::generateKey()` produces a key. Implement
  `District5\Mondoc\Extensions\FieldEncryption\MondocEncryptionInterface` (`encrypt`/`decrypt`) for
  your own.
- Encryption happens in `asArray(true)`, after alias mapping and after `DateTime`→`UTCDateTime`
  conversion; decryption happens per-key during inflation. Aliases and encryption compose correctly.
- **Encrypted fields are opaque to the database**: you cannot filter, sort, or aggregate on them, and
  range queries are meaningless. Keep anything you need to query unencrypted.
- Encrypting a whole nested sub-model is not supported — encrypt the fields inside it instead.

---

## 5. Services

```php
use District5\Mondoc\Db\Service\MondocAbstractService;

class UserService extends MondocAbstractService
{
    protected static function getCollectionName(): string { return 'users'; }

    // optional — defaults to 'default'
    protected static function getConnectionId(): string { return 'default'; }

    // optional — named reusable filters
    protected static function getScopes(): array
    {
        return [
            'adults' => ['age' => ['$gte' => 18]],
            'active' => ['active' => true],
        ];
    }
}
```

`getCollectionName()` is the only abstract member. Everything below is inherited.

Base-class methods: `getMongo(): ?Database`, `getCollection(?string $clz = null): Collection`,
`aggregate(): AggregateSubService`, `getQueryBuilder(): QueryBuilder`,
`getMondocModelClass(): string`, `mergeFilters(array ...$f): array`, `scope(string ...$names): array`.

### 5.1 Reads

| Method                                                                          | Returns                                                              |
|---------------------------------------------------------------------------------|----------------------------------------------------------------------|
| `getById(ObjectId\|string $id)`                                                 | `?MondocAbstractModel` — `null` if the id is unparseable or missing  |
| `getOneByCriteria(array $filter = [], array $options = [])`                     | `?Model`                                                             |
| `getOneByQueryBuilder(QueryBuilder $b)`                                         | `?Model`                                                             |
| `getOneWhereKeyEqualsValue(string $k, mixed $v)`                                | `?Model` (uses `$eq`)                                                |
| `getOneWhereKeyDoesNotEqualValue(string $k, mixed $v)`                          | `?Model` (uses `$ne`)                                                |
| `getByIds(array $ids)`                                                          | `Model[]` — accepts strings and `ObjectId`s; invalid ids are dropped |
| `getMultiByCriteria(array $filter = [], array $options = [])`                   | `Model[]`                                                            |
| `getMultiByQueryBuilder(QueryBuilder $b)`                                       | `Model[]`                                                            |
| `getMultiWhereKeyEqualsValue(string $k, mixed $v)`                              | `Model[]`                                                            |
| `getMultiWhereKeyDoesNotEqualValue(string $k, mixed $v)`                        | `Model[]`                                                            |
| `getDistinctValuesForKey(string $key, array $filter = [], array $options = [])` | `array`                                                              |

`$options` is passed straight to the driver: `sort`, `limit`, `skip`, `projection`, `collation`, etc.

Beware **projections**: a projected-away field is absent from the BSON, so the model's property keeps
its PHP default and — because the original BSON lacks the key — is treated as dirty. Saving a
projected model can write defaults over real data. Only save models you loaded in full.

`getOneByCriteria()` with a `sort` option internally routes through `getMultiByCriteria()` with
`limit => 1`; without a sort it uses `findOne()`.

### 5.2 Counting and existence

| Method                                              | Returns                                            |
|-----------------------------------------------------|----------------------------------------------------|
| `countAll(array $filter = [], array $options = [])` | `int`                                              |
| `countAllByQueryBuilder(QueryBuilder $b)`           | `int`                                              |
| `countWhereKeyEqualsValue(string $k, mixed $v)`     | `int`                                              |
| `estimateDocumentCount(array $options = [])`        | `int` — collection metadata, ignores filters, O(1) |
| `exists(array $criteria, array $options = [])`      | `bool`                                             |
| `existsById(ObjectId\|string $id)`                  | `bool`                                             |
| `existsWithQueryBuilder(QueryBuilder $b)`           | `bool`                                             |

### 5.3 Writes

| Method                                                       | Returns  | Notes                                                                                                                                                                     |
|--------------------------------------------------------------|----------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `saveModel(Model $m, array $opts = [])`                      | `bool`   | Insert-or-update. What `$model->save()` calls                                                                                                                             |
| `insert(Model $m, array $opts = [])`                         | `bool`   | Fires `beforeInsert`/`afterInsert`                                                                                                                                        |
| `insertMulti(Model[] $ms, array $opts = [])`                 | `bool`   | One `insertMany` per service; routes models to their own services by the service map. Vetoed models skipped. `true` for an empty array; `false` if every model was vetoed |
| `update(Model $m, array $opts = [])`                         | `bool`   | Dirty-field `$set` + `$unset` of removed keys. `false` if the model has no `_id`; `true` no-op when clean                                                                 |
| `updateOne(array $filter, array $update, array $opts = [])`  | `bool`   | Raw. **No hooks, no dirty tracking, in-memory models not refreshed**                                                                                                      |
| `updateMany(array $filter, array $update, array $opts = [])` | `bool`   | Raw                                                                                                                                                                       |
| `updateOneByQueryBuilder(QueryBuilder $b, array $update)`    | `bool`   |                                                                                                                                                                           |
| `updateManyByQueryBuilder(QueryBuilder $b, array $update)`   | `bool`   |                                                                                                                                                                           |
| `updateManyModels(Model[] $ms, array $opts = [])`            | `bool[]` | Per-model `update()`, routed by service map. Models without an `_id` are skipped, so **the result array is not index-aligned with the input**                             |

`updateOne`/`updateMany` take a *full update document* — you supply the operator:
`['$set' => ['age' => 2]]`.

### 5.4 Deletes

| Method                                               | Returns                                               | Hooks                              |
|------------------------------------------------------|-------------------------------------------------------|------------------------------------|
| `deleteModel(Model $m)`                              | `bool` — also clears the model's `_id` and collection | yes                                |
| `delete(ObjectId\|string $id)`                       | `bool`                                                | no                                 |
| `deleteOne(array $filter, array $options = [])`      | `bool`                                                | no                                 |
| `deleteMulti(array $filter, array $options = [])`    | `int` deleted count                                   | no                                 |
| `deleteByIds(array $objectIds, array $options = [])` | `int` — de-duplicates ids                             | no                                 |
| `deleteModels(Model[] $ms)`                          | `int`                                                 | **no** — batches by id per service |

### 5.5 Atomic operations

Server-side `$inc`, so safe under concurrency.

On the **service** (`ObjectId` first):

```php
UserService::inc($id, 'logins');                        // +1
UserService::inc($id, 'score', 10);
UserService::dec($id, 'credits', 5);                    // negative $inc
UserService::incMulti($id, ['age' => 1, 'logins' => 1]);
UserService::decMulti($id, ['credits' => 5, 'quota' => 1]);
```

On the **model**:

```php
$user->inc('logins');        // updates DB and the in-memory property
$user->dec('credits', 5);
```

Model-level `inc`/`dec` on an **unsaved** model mutate the property in memory and return `false` —
a falsy return does not mean nothing changed locally.

`protected static function atomic(ObjectId $id, array $update): bool` is the escape hatch for any
other operator; expose it from your own service if you need it.

### 5.6 Array operators (`$push` / `$pull`)

These are **`protected`** — deliberately. Wrap them in your service:

```php
class UserService extends MondocAbstractService
{
    protected static function getCollectionName(): string { return 'users'; }

    public static function addTag(ObjectId $id, string $tag): bool
    {
        return self::pushIntoArrayById($id, 'tags', $tag, distinct: true);
    }

    public static function removeTag(ObjectId $id, string $tag): bool
    {
        return self::pullFromArrayById($id, 'tags', $tag);
    }
}
```

| Method                                                                                 | Notes                                                                                   |
|----------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------|
| `pushIntoArrayById(ObjectId $id, string $field, string $value, bool $distinct = true)` | `$distinct` adds `$ne` to the filter so duplicates are not appended. Scalar values only |
| `pushIntoArrayWithFilter(array $filter, string $field, mixed $value)`                  | Accepts any value                                                                       |
| `pullFromArrayById(ObjectId $id, string $field, mixed $value)`                         |                                                                                         |
| `pullFromArrayWithFilter(array $filter, string $field, mixed $value)`                  |                                                                                         |

### 5.7 Query scopes

```php
UserService::scope('adults');                      // ['age' => ['$gte' => 18]]
UserService::scope('adults', 'active');            // array_merge of both
UserService::mergeFilters(
    UserService::scope('adults', 'active'),
    ['country' => 'GB']
);
```

`scope()` merges the named scopes from `getScopes()` and **silently ignores unknown names** (a typo
yields a broader query, not an error). Merging is a shallow `array_merge`, so two scopes touching the
same key overwrite rather than combine — use `$and` explicitly if you need that:

```php
['$and' => [UserService::scope('adults'), ['age' => ['$lte' => 65]]]]
```

### 5.8 Aggregation

Reached via `Service::aggregate()`, which returns an `AggregateSubService`.

| Method                                                                                                                                                                                                                           | Returns                                                                                                                                                                                                    |
|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `getAverage(string $field, array $filter = [])`                                                                                                                                                                                  | `float\|int` — `0` when there is no match                                                                                                                                                                  |
| `getSum(string $field, array $filter = [])`                                                                                                                                                                                      | `float\|int`                                                                                                                                                                                               |
| `getMin(string $field, array $filter = [])`                                                                                                                                                                                      | `mixed` (works on strings too)                                                                                                                                                                             |
| `getMax(string $field, array $filter = [])`                                                                                                                                                                                      | `mixed`                                                                                                                                                                                                    |
| `getPercentile(string $field, float $percentile, int $sortDirection = 1, array $filter = [])`                                                                                                                                    | `float\|int\|null` — percentile as a fraction (`0.1` = 10th). Pushes every matched value into one array server-side, so it is bounded by the 16MB BSON document limit; filter it down on large collections |
| `getFinancialXMinuteCandles(array $filter, array $group, string $priceField, string $dateField, int $minuteGranularity, int $sortDirection, ?int $limitNumber = null)`                                                           | `AggregateFinancialCandleDto[]`                                                                                                                                                                            |
| `getFinancialSma(string $symbolField, string $symbolValue, DateTime $from, DateTime $to, string $dateField, string $priceField, int $numGroupMinutes, int $smaNumPeriods, int $sortDirection = 1, array $additionalFilter = [])` | `AggregateSmaDto[]`                                                                                                                                                                                        |

```php
$avgAge = UserService::aggregate()->getAverage('age', ['active' => true]);
$p90    = UserService::aggregate()->getPercentile('age', 0.9);
```

DTOs are read-only: `AggregateFinancialCandleDto` → `getHigh/getLow/getOpen/getClose/getDate`;
`AggregateSmaDto` → `getPrice/getSma/getDate`.

For anything else, drop to the driver: `Service::getCollection()->aggregate([...])`.

### 5.9 Pagination

Two independent strategies.

**Skip/limit (page numbers)** — supports jumping to any page; slow on deep pages.

```php
$paginator = UserService::getPaginationHelper(
    currentPageNumber: 1,
    perPage: 10,
    filter: ['active' => true]
);
$page = UserService::getPage($paginator, '_id', -1);
// or with full driver options (skip/limit filled in if absent):
$page = UserService::getPageWithOptions($paginator, ['sort' => ['name' => 1]]);
```

**ObjectId cursor** — constant cost, no page numbers.

```php
$paginator = UserService::getPaginationHelperForObjectIdPagination(10, ['active' => true]);
$page = UserService::getPageByByObjectIdPagination($paginator, null, -1);   // first page
$last = end($page);
$next = UserService::getPageByByObjectIdPagination($paginator, $last->getObjectId(), -1);
```

`$sortDirection` must be `1` or `-1`; anything else throws `InvalidArgumentException`.

Since 6.3.0 the **filter is carried by the paginator** — do not pass it again to `getPage*`.

`getPaginationHelperByQueryBuilder(QueryBuilder $b, int $currentPage, int $perPage)` builds one from a
query builder.

`MondocPaginationHelper` API: `getCurrentPage()`, `getLimit()` (the per-page value), `getSkip()`,
`getTotalPages()`, `getTotalResults()`, `isFirstPage()`, `isLastPage()`, `hasPreviousPage()`,
`hasNextPage()`, `hasMultiplePages()`, `getFilter()`, `setFilter(array)`,
`shouldShowPageOnUi(int $proposed, ?int $howManyPagesEachSide)`, `asArray()`, and the static
`init(int $totalResults, int $currentPage, int $perPage = 10)`.

Each `getPaginationHelper*` call runs a `countAll()`. Reuse the helper across a request rather than
rebuilding it.

---

## 6. Dates

`FilterFormatter::format()` recursively converts every `DateTime` in a filter or update to
`UTCDateTime` before it reaches the driver, so these are identical:

```php
UserService::getMultiByCriteria(['lastLogin' => ['$lte' => new DateTime()]]);
UserService::getMultiByCriteria([
    'lastLogin' => ['$lte' => MondocTypes::phpDateToMongoDateTime(new DateTime())]
]);
```

On the model side, `UTCDateTime` values inflate to `DateTime`, and `DateTime` properties convert back
to `UTCDateTime` on write. Application code can stay in `DateTime` throughout.

Use `district5/date` for construction and maths — `Date::nowUtc()`, `Date::mongo()->convertTo($d)`,
`Date::modify($d)->plus()->days(30)`. Store UTC.

---

## 7. Query building (`district5/mondoc-builder`)

```php
use District5\MondocBuilder\QueryBuilder;
use District5\MondocBuilder\QueryTypes\ValueEqualTo;
use District5\MondocBuilder\QueryTypes\ValueGreaterThan;

$builder = new QueryBuilder();               // or UserService::getQueryBuilder()

$eq = new ValueEqualTo();
$eq->string('n', 'Alice');                   // NOTE: DB key, not the PHP property name
$builder->addQueryPart($eq);

$gt = new ValueGreaterThan();
$gt->integer('age', 18);
$builder->addQueryPart($gt);

$builder->getOptions()->setSortBySimple('age', -1)->setLimit(20);

$users = UserService::getMultiByQueryBuilder($builder);
```

Query part classes (`District5\MondocBuilder\QueryTypes\`): `ValueEqualTo`, `ValueNotEqualTo`,
`ValueGreaterThan`, `ValueGreaterThanOrEqualTo`, `ValueLessThan`, `ValueLessThanOrEqualTo`,
`ValueInValues`, `ValueNotInValues`, `HasAllValues`, `KeyExists`, `RegexMatch`, `SizeOfValue`,
`AndOperator`, `OrOperator`, `NorOperator`, `GeospatialPointNear`, `GeospatialPointNearSphere`.

Value setters on the equality/comparison parts: `string()`, `integer()`, `float()`, `double()`,
`boolean()`, `null()`, `objectId()`, `dateTime()`, `utcDateTime()`, `mongoNative()`.

`QueryBuilder`: `addQueryPart(AbstractQueryType)`, `addCustomArrayPart(array)`, `getArrayCopy()`,
`getOptions(): QueryOptions`, `setOptions(QueryOptions)`.
`QueryOptions`: `setLimit()`, `setSkip()`, `setSortBy(array)`, `setSortBySimple(string, int)`,
`setProjection()`, `setCustom(array)`, `getArrayCopy()`.

**The builder is alias-blind** — it operates on database keys. If a model aliases `n => name`, the
builder must be given `n`.

---

## 8. Type conversion — `MondocTypes`

`District5\Mondoc\Helper\MondocTypes`, all static.

```php
MondocTypes::phpDateToMongoDateTime(new DateTime()): ?UTCDateTime
MondocTypes::dateToPHPDateTime($utcDateTime): ?DateTime

MondocTypes::arrayToPhp($bsonDocumentOrArray): mixed   // recursive BSON → plain PHP
MondocTypes::typeToJsonFriendly($anything): mixed      // recursive JSON-safe conversion

MondocTypes::newObjectId(): ObjectId
MondocTypes::newObjectIdFromDateObject(DateTime|UTCDateTime $d): ObjectId
MondocTypes::objectIdToString(ObjectId $id): string
MondocTypes::toObjectId(ObjectId|array|string|null $id): ?ObjectId
MondocTypes::deduplicateArrayOfObjectIds(array $ids): ObjectId[]
MondocTypes::arrayOfObjectIdsToObjectIds(array $data): ObjectId[]
```

`toObjectId()` accepts a 24-char string, an existing `ObjectId`, or `['oid' => '…']` /
`['$oid' => '…']`, and returns **`null`** for anything else — including a malformed string. It never
throws, so validate the result rather than assuming success.

`typeToJsonFriendly()` handles `BSONArray`/`BSONDocument`, `UTCDateTime`/`DateTime` (→ `'Uv'`
string), `ObjectId` (→ string), `Decimal128`, `Timestamp`, backed enums (→ `->value`), `stdClass`,
and `MondocAbstractSubModel` (→ `asArray()`), recursing through arrays.

Also available: `HasTraitHelper::has(object $o, string $trait): bool` (recursive trait check, used to
detect the opt-in traits) and `FilterFormatter::format(array $filter): array`.

---

## 9. Data retention (change history)

Add `MondocRetentionTrait` to a model and Mondoc snapshots the whole document into a separate
`mondoc_retention` collection on **every** insert and update.

```php
use District5\Mondoc\Db\Model\Traits\MondocRetentionTrait;
use District5\Mondoc\Extensions\Retention\MondocRetentionService;
use District5\Date\Date;

class ContractModel extends MondocAbstractModel
{
    use MondocRetentionTrait;
    protected string $terms = '';
}

$c = new ContractModel();
$c->setMondocRetentionChangeMeta(['user' => 'joe.bloggs', 'reason' => 'initial']);
$c->setMondocRetentionExpiry(Date::modify(Date::nowUtc())->plus()->days(30));  // or null = never
$c->save();     // one ContractModel + one MondocRetentionModel
```

**Setup requirement:** `MondocRetentionService` uses connection id **`mondoc_retention`**, so you
must register a database under that id or every retentioned save throws
`MondocConfigConfigurationException`:

```php
$config->addDatabase($client->selectDatabase('app'), 'mondoc_retention');
```

Call `MondocRetentionService::addIndexes()` once during deployment (`hasIndexes()` to check) — it is
never automatic.

Mondoc **never deletes expired retention data**. Expiry is advisory; you query for it and delete it.

### `MondocRetentionService`

| Method                                                                                                  | Notes                                                                                 |
|---------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------|
| `create(Model $m): void`                                                                                | Snapshot + save. Called automatically                                                 |
| `createStub(Model $m): MondocRetentionModel`                                                            | Unsaved snapshot (used by `insertMulti`)                                              |
| `getLatestRetentionModelForModel(string $class, ObjectId $id): ?MondocRetentionModel`                   |                                                                                       |
| `countRetentionModelsForClassName(string $class): int`                                                  |                                                                                       |
| `countRetentionModelsForModel(Model $m): int`                                                           |                                                                                       |
| `getRetentionHistoryPaginationHelperForClassName(string\|Model $class, int $perPage, int $currentPage)` | ⚠ `$perPage` **before** `$currentPage`                                               |
| `getRetentionHistoryPaginationHelperForModel(Model $m, int $perPage, int $currentPage)`                 | ⚠ same order                                                                         |
| `getPaginatorForExpiredRetentionForClassName(string\|Model $class, int $currentPage, int $perPage)`     | ⚠ `$currentPage` **before** `$perPage`                                               |
| `getPaginatorForExpiredRetentionForObject(Model $m, int $currentPage, int $perPage)`                    | ⚠ same order                                                                         |
| `getRetentionPage(MondocPaginationHelper $p, string $sortByField = '_id', int $sortDirection = -1)`     | `MondocRetentionModel[]`. A thin alias of the inherited `getPage()`, which also works |
| `addIndexes(): void` / `hasIndexes(): bool`                                                             |                                                                                       |

The `$perPage`/`$currentPage` argument order is **inconsistent between the history and expiry
methods**. Use named arguments.

### On the model itself (`MondocRetentionTrait`)

| Method                                                                                                                                 | Notes                                                                                   |
|----------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------|
| `setMondocRetentionChangeMeta(array $data): static`                                                                                    | Arbitrary metadata stored alongside the snapshot (who changed it, why). No preset shape |
| `getMondocRetentionChangeMeta(): array`                                                                                                |                                                                                         |
| `setMondocRetentionExpiry(DateTime\|UTCDateTime\|null $expiry): static`                                                                | `null` = never expires                                                                  |
| `getMondocRetentionExpiry(): DateTime\|UTCDateTime\|null`                                                                              |                                                                                         |
| `getRetentionPaginatorForModel(int $perPage, int $currentPage): MondocPaginationHelper`                                                | This model's own history                                                                |
| `getRetentionPageByPaginator(MondocPaginationHelper $p, string $sortByField = '_id', int $sortDirection = -1): MondocRetentionModel[]` |                                                                                         |

`$model->isMondocRetentionEnabled(): bool` (on every model) reports whether the trait is present.

### `MondocRetentionModel`

`toOriginalModel(): MondocAbstractModel` (the snapshot re-inflated), `getSourceModelData(): array`,
`getSourceObjectId(): ObjectId`, `getSourceObjectIdString(): string`, `getSourceClassName(): string`,
`getRetentionData(): array`, `getRetentionExpiry(bool $asMongo = false)`, `hasRetentionExpired(): bool`.
It uses `MondocCreatedDateTrait`, so snapshots are ordered by `cd`.

Cost note: every save writes a second full document. Not suitable for hot-path or high-write models.

---

## 10. Exceptions

All extend `District5\Mondoc\Exception\MondocException`.

| Exception                            | Thrown when                                              |
|--------------------------------------|----------------------------------------------------------|
| `MondocServiceMapErrorException`     | The model→service (or service→model) mapping is missing  |
| `MondocConfigConfigurationException` | The requested connection id has no registered `Database` |
| `MondocException`                    | Base; also the generic case                              |

Driver-level failures surface as `MongoDB\Driver\Exception\*` (e.g. `BulkWriteException` on a unique-index
violation) — Mondoc does not wrap them. `getPageByByObjectIdPagination()` throws a plain
`InvalidArgumentException` for a bad sort direction.

Practically every Mondoc method is annotated with at least
`MondocConfigConfigurationException`, `MondocServiceMapErrorException` and `MondocException`.

---

## 11. Gotchas

1. **Unregistered model → `MondocServiceMapErrorException`.** First thing to check on any failure.
2. **Retention needs a `mondoc_retention` database registered.** Otherwise every retentioned save
   throws.
3. **No encryption adapter = silent plaintext.** No warning.
4. **Filters use DB keys, not property names.** Aliases are not applied to query input, sorts,
   projections, or query-builder parts.
5. **`update()` returning `true` may mean "nothing to do".** Clean models short-circuit before the
   query and fire no hooks.
6. **Never save a projected model.** Missing fields look dirty at their PHP defaults and will be
   written over real data.
7. **`deleteModels()` skips `beforeDelete()`/`afterDelete()`.** So do all raw `delete*`/`update*`
   methods. Only `deleteModel()`, `insert()`, `insertMulti()` and `update()` fire hooks.
8. **`insertMulti()` silently skips vetoed models.** Test `hasObjectId()` afterwards.
9. **`updateManyModels()` returns a non-aligned `bool[]`.** Models without an `_id` are skipped
   entirely, so index *n* of the result is not necessarily model *n*.
10. **Single-nested models are auto-instantiated in the constructor.** `$model->car` is a
    `CarSubModel`, not `null`, before any data is loaded.
11. **`clone()` without a target class is shallow.** Nested sub-models are shared by reference; pass
    a class to force a deep copy.
12. **`push`/`pull`/`atomic` are `protected`.** Expose them from your own service.
13. **`removeField()` only removes unmapped fields.** Unset a declared property by setting it to its
    default and saving (dirty tracking `$unset`s keys that vanished from `asArray()`).
14. **`toObjectId()` returns `null` on bad input** rather than throwing.
15. **`MondocConfig` is a process-wide singleton.** In long-running workers (Swoole, RoadRunner,
    queue consumers) it persists across requests; `reconstruct()` resets it.
16. **Encrypted fields cannot be queried, sorted, or aggregated.**
17. **`estimateDocumentCount()` ignores filters.** It reads collection metadata.
18. **Retention/history paginator argument order is inconsistent.** Use named arguments.
19. **Private properties are not persisted.** Intentional — but a surprise if you make a field
    private and its data stops saving.
20. **JSON dates are `'Uv'` millisecond strings**, not ISO-8601.

---

## 12. Recipes

**Full model + service pair**

```php
namespace App\Model;

use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Model\Traits\MondocCreatedDateTrait;
use District5\Mondoc\Db\Model\Traits\MondocModifiedDateTrait;

class UserModel extends MondocAbstractModel
{
    use MondocCreatedDateTrait;
    use MondocModifiedDateTrait;

    protected string $email = '';
    protected string $name = '';
    protected int $age = 0;
    protected bool $active = true;
    protected array $tags = [];

    protected array $mondocFieldAliases = ['e' => 'email', 'n' => 'name'];

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): static { $this->email = strtolower(trim($v)); return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $v): static { $this->name = trim($v); return $this; }
    public function getAge(): int { return $this->age; }
    public function setAge(int $v): static { $this->age = $v; return $this; }
    public function isActive(): bool { return $this->active; }

    public function beforeInsert(): bool { return $this->email !== ''; }
}
```

```php
namespace App\Service;

use App\Model\UserModel;
use District5\Mondoc\Db\Service\MondocAbstractService;

class UserService extends MondocAbstractService
{
    protected static function getCollectionName(): string { return 'users'; }

    protected static function getScopes(): array
    {
        return ['active' => ['active' => true], 'adults' => ['age' => ['$gte' => 18]]];
    }

    public static function getByEmail(string $email): ?UserModel
    {
        // 'e' — the DB key, because email is aliased
        return self::getOneWhereKeyEqualsValue('e', strtolower(trim($email)));
    }

    public static function addTag(\MongoDB\BSON\ObjectId $id, string $tag): bool
    {
        return self::pushIntoArrayById($id, 'tags', $tag);
    }
}
```

**Read-modify-write**

```php
$user = UserService::getById($id);
if ($user === null) { /* not found */ }
$user->setName('Alice')->setAge(31);
$user->save();                        // one $set with only name + age (+ md, _rn if used)
```

**Counter that is safe under concurrency**

```php
$user->inc('logins');                 // server-side $inc, not read-modify-write
```

**Iterate a large collection without loading it all**

```php
$paginator = UserService::getPaginationHelperForObjectIdPagination(500);
$cursorId = null;
do {
    $batch = UserService::getPageByByObjectIdPagination($paginator, $cursorId, 1);
    foreach ($batch as $user) { /* ... */ }
    $cursorId = $batch === [] ? null : end($batch)->getObjectId();
} while ($batch !== []);
```

**Something Mondoc does not cover — drop to the driver**

```php
$collection = UserService::getCollection();          // MongoDB\Collection
$collection->createIndex(['e' => 1], ['unique' => true]);
$results = $collection->aggregate([['$match' => [...]], ['$group' => [...]]]);
```

`UserService::getMongo()` gives the `MongoDB\Database`.

---

## Corrections to published docs

Verified against `src/` at `7.6.1`. Prefer this file where they conflict.

| Published                                                                                                   | Actual                                                                                                                                                                                |
|-------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| README: `$model->setMondocRetentionData([...])`                                                             | `setMondocRetentionChangeMeta(array $data)`                                                                                                                                           |
| README: `getPageByByObjectIdPagination($paginator, $currentId, $perPage, $sortDirection)`                   | `getPageByByObjectIdPagination(MondocPaginationHelper $paginator, ObjectId\|string\|null $currentId, int $sortDirection = -1)` — three parameters; `perPage` comes from the paginator |
| README: `new \District5\Mondoc\Db\Builder\MondocBuilder\MondocBuilder()` with `->addFilter('name', 'John')` | `new District5\MondocBuilder\QueryBuilder()`; no `addFilter()` — build parts and call `addQueryPart()`                                                                                |
| README: nested-model comments claiming `BSONDocument`/`BSONArray` in the property type "is important"       | Not required since 5.1.0; the surrounding prose says so, the inline comments were not updated                                                                                         |
