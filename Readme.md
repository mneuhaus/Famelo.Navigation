# Famelo.Navigation

This package uses a combination of Routes.yaml and Policy.yaml to generate a menu for you :)

```yaml
-
  name: 'Home'
  uriPattern: 'home'
  navigation: 'Home'
  defaults:
    '@format': 'html'
    '@action':     'index'
    '@controller': 'Standard'
    '@package': 'My.Package'
  appendExceedingArguments: true

-
  name: 'Products'
  uriPattern: 'products'
  navigation: 'Products'
  defaults:
    '@format': 'html'
    '@action':     'index'
    '@controller': 'Products'
    '@package': 'My.Package'
  appendExceedingArguments: true

-
  name: 'Product in Foo Category'
  uriPattern: 'products/foo'
  navigation: 'Foo'
  defaults:
    '@format': 'html'
    '@action':     'foo'
    '@controller': 'Products'
    '@package': 'My.Package'
  appendExceedingArguments: true
```

That routes will be parsed based on the paths into:
```
Home
Products
  - Foo
```

Now let's render a menu from that information:
```html
{namespace n=Famelo\Navigation\ViewHelpers}
<ul class="navigation">
<n:navigation as="items">
<f:for each="{items}" as="item">
  <li>
      <n:action actionConfiguration="{item}">{item.label}</n:action>
      <f:if condition="{item.children}">
        <ul>
        <f:for each="{item.children}" as="child">
          <li>
              <n:action actionConfiguration="{child}">{child.label}</n:action>
          </li>
        </f:for>
        </ul>
      </f:if>
  </li>
</f:for>
</n:navigation>
</ul>
```

Something you don't see, which happens in the background is, that each NavigationItem is checked through the Policy.yaml if the current user has access to it.
So, if you add a Policy.yaml like this:
```yaml
resources:
  methods:
    Products: 'method(My\Package\Controller\ProductsController->.*Action())'

roles:
  Administrator: []

acls:
  Administrator:
    methods:
      Products: GRANT
```

Only the Logged in Administrator will see the Products NavigationItems :)