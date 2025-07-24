<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class ActivityObserver
{
    public function created(Model $model)
    {
        activity(class_basename($model)) // log_name
            ->performedOn($model)
            ->causedBy(auth()->user())
            ->event('created')
            ->withProperties($model->getAttributes())
            ->log('created');
    }

    public function updated(Model $model)
    {
        activity(class_basename($model))
            ->performedOn($model)
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties($model->getChanges())
            ->log('updated');
    }

    public function deleted(Model $model)
    {
        activity(class_basename($model))
            ->performedOn($model)
            ->causedBy(auth()->user())
            ->event('deleted')
            ->log('deleted');
    }
}
