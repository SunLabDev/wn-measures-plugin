## Measures
This plugin allows you to create/increment and display any measures you want on a model, some examples could be:
- Blog post's views
- API's resource fetches count
- ...

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

#### Practical example, creating a post views in Winter.Blog:
```
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
You can increment multiple models measure at once, this is useful when you want to measure the amount of models fetches from an API.
```php
// Passing to the MeasureManager a Builder instance
$products = Product::where('name', 'like', '%shoes%');
MeasureManager::incrementMeasure($products, 'appearedInSearchResults');

return new JsonResponse([
    'products' => $products->get()
]);
```

### Displaying a measure
To display the measure from a model, just use the `getMeasure` method on it.
The method returns a Measure model which contains an `amount` property
```php
// From a backend controller/view
$post = Post::first();
$views = $post->getMeasure('views')->amount;
```

```html
// From a frontend Twig block
{{ post.getMeasure('view').amount }}
```

### TODO-List:
In a near future, I'll add some feature such as:
- [ ] Bulk incrementation from a model collection instead of the Builder
- [ ] Easier access to the amount of a measure using `$model->getViewsMeasure()`
- [ ] A ReportWidget displaying some specific measures

[Storm extension README]: https://github.com/wintercms/storm/blob/develop/src/Extension/README.md
