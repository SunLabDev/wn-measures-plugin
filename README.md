## Measures
This plugin allows you to create/increment and display any measures you want on a model, some examples could be:
- Blog post's views
- Number of forum topic creation from a member
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

// Using the MeasureManager statics helper:
use SunLab\Measures\Classes\MeasureManager;
MeasureManager::incrementMeasure($post, 'views');
MeasureManager::incrementMeasure($post, 'views', 5);
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
    Event::listen(function() {
        MeasureManager::incrementOrphanMeasure('users_login');

        // incrementMeasure also support orphan measure.
        // Same as:
        MeasureManager::incrementMeasure('users_login');
    });
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

### TODO-List:
In a near future, I'll add some feature such as:
- [ ] Allow decrementing a measure
- [ ] Bulk incrementation from a model collection instead of the Builder
- [ ] A ReportWidget displaying some specific measures

[Storm extension README]: https://github.com/wintercms/storm/blob/develop/src/Extension/README.md
