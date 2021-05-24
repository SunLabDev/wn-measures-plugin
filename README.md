## Measures
This plugin allows you to create, update and display any measures you want on a model, some examples could be:
- Blog post's views
- Number of forum topic creation from a member
- Counting successive daily-connexion of a member
- API's resource fetches count
- ...

This plugin is intended to be used in more complex plugins or as is to register some statistics over your website's events.

### Measurable Behavior
You can add the Measurable Behavior (think of it as a Trait) to any model you want. Using [Storm Extension][Storm extension README].

Extending existing models from a Plugin registration file:
```php
Winter\Blog\Models\Post::extend(function ($postModel) {
    $postModel->implement[] = 'SunLab.Measures.Behaviors.Measurable';
});
```
Or directly to your Plugin's model:
```php
class Link extends Model
{
    public $implement = ['SunLab.Measures.Behaviors.Measurable'];
    //...
}
```

### Create/Increment a measure
Once you've added the Measurable Behavior to a model, you can use the model's method `incrementMeasure`.

```php
$post->incrementMeasure('views');

// Optional amount of incrementation can be passed
$post->incrementMeasure('views', 5);
```
> Note: You don't have to process any kind of _initialization_ of the measure, just use it.

#### Practical example 1, count how many times a user edit his posts
```php
// In Plugin.php file
function boot()
{
    // Add the Measurable behavior to User model
    Winter\User\Models\User::extend(function($user) {
        // Verify it has not been already added by another plugin
        if (!$user->isClassExtendedWith('SunLab.Measures.Behaviors.Measurable')) {
            $user->extendClassWith('SunLab.Measures.Behaviors.Measurable');
        }
    });

    Winter\Forum\Models\Post::extend(function($post) {
        // Bind listener to update event
        $post->bindEvent('model.afterUpdate', function() use ($model) {
            // Increment measure on the member
            $post->member->incrementMeasure('post_edit');
        });
    });
}
```

#### Practical example 2, creating a post views in Winter.Blog:
```php
title = "Blog post page"
url = "/blog/article/:slug"
layout = "default"
is_hidden = 0

[blogPost]
slug = "{{ :slug }}"
categoryPage = "blog/category"
==
function onEnd() {
    $this->blogPost->post->incrementMeasure('views');
}
==
{% component 'blogPost' %}
```

### Bulk incrementation
You can increment multiple models measure at once,
this is useful when you want to measure the amount of models fetches from an API.

To use Bulk incrementation, you need to pass a Builder instance of your query to the MeasureManager:
```php
// Passing to the MeasureManager a Builder instance
$products = Product::where('name', 'like', '%shoes%');
MeasureManager::incrementMeasure($products, 'appearedInSearchResults');

return new JsonResponse([
    'products' => $products->get()
]);
```

### Orphan measures
For some reason, you may want to increment some orphan measures:
```php
    // Count how many users log-in
    Event::listen('winter.user.login', function() {
        MeasureManager::incrementOrphanMeasure('users_login');

        // incrementMeasure also support orphan measure.
        // Same as:
        MeasureManager::incrementMeasure('users_login');
    });
```

### Decrement or reset a measure
The plugin support both decrement and reset on measures, related to model or orphan measure:
```php
    // On model implementing Measurable
    $model->decrementMeasure('post_edit');
    $model->decrementMeasure('post_edit', 3); // An amount of decrementation can be passed

    // With orphan measures
    MeasureManager::decrementOrphanMeasure('users_login');
    MeasureManager::decrementOrphanMeasure('users_login', 3); // An amount of decrementation can be passed
```

### Displaying a measure
To display the measure from a model, just use the `getMeasure` or `getAmountOf` methods on it.
`getMeasure` returns a Measure model which contains an `amount` property
```php
// From a backend controller/view
$post = Post::first();
$views = $post->getMeasure('views')->amount;
// Same as:
$views = $post->getAmountOf('views');
```

```twig
// From a frontend Twig block
{{ post.getMeasure('view').amount }}
// Same as:
{{ post.getAmountOf('view') }}
```

### MeasureManager and models
The `MeasureManager` static class handles orphan and bulk measures modification,
but can also increment model measure:
```php
// Using the MeasureManager static helpers:
use SunLab\Measures\Classes\MeasureManager;
    $post = \Winter\Blog\Models\Post::first();
    // Increment a model's measure
    MeasureManager::incrementMeasure($post, 'views');
    MeasureManager::incrementMeasure($post, 'views', 5);
    // Decrement:
    MeasureManager::decrementMeasure($model, 'views');
    MeasureManager::decrementMeasure($model, 'views', 3);
    // Reset
    MeasureManager::resetMeasure('views');
    MeasureManager::resetMeasure('views', 2);
```

### TODO-List:
In a near future, I'll add some feature such as:
- [ ] Bulk incrementation from a model collection instead of the Builder
- [ ] A ReportWidget displaying some specific measures

[Storm extension README]: https://github.com/wintercms/storm/blob/develop/src/Extension/README.md
