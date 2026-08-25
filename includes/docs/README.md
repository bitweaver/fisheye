# Fisheye package documentation

> Engineering documentation derived from the source in this package. The
> package's `includes/` directory must be denied to direct HTTP requests.

## Purpose

Fisheye provides image galleries and image-oriented Liberty content.

## Responsibility

Owns image and gallery models, gallery traversal, image upload/display workflows, and image-specific metadata.

## Dependencies

kernel, liberty, users, themes, util.

Dependency direction matters: this package may depend on the packages above;
the dependencies do not thereby depend on this package.

## Boundary

Does not own generic attachment storage or site-specific image production workflows.

## Documentation map

- [Architecture](architecture.md) — initialization, components, and request flow.
- [Source reference](source-reference.md) — source-derived files, classes,
  controllers, schema artifacts, plugins, and templates.
- [Development guide](development.md) — safe change workflow, extension points,
  validation, and maintenance guidance.
- [Security](security.md) — trust boundaries and direct-HTTP access requirements.
- [Gallery and image lifecycle](gallery-image-lifecycle.md) — content classes,
  mappings, uploads, storage, metadata, thumbnails, transforms, and deletion.
