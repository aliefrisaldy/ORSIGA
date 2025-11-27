<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $table = 'sites';

    protected $fillable = [
        'site_id',
        'site_name',
        'description',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function siteReports()
    {
        return $this->hasMany(SiteReport::class, 'site_id', 'id');
    }


    public function scopeNearby($query, $latitude, $longitude, $radiusKm = 10)
    {
        $haversine = "(6371 * acos(cos(radians(?)) 
                     * cos(radians(latitude)) 
                     * cos(radians(longitude) - radians(?)) 
                     + sin(radians(?)) 
                     * sin(radians(latitude))))";

        return $query
            ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude])
            ->whereRaw("{$haversine} < ?", [$latitude, $longitude, $latitude, $radiusKm])
            ->orderBy('distance');
    }

    public function scopeWithinBounds($query, $minLat, $maxLat, $minLng, $maxLng)
    {
        return $query->whereBetween('latitude', [$minLat, $maxLat])
                    ->whereBetween('longitude', [$minLng, $maxLng]);
    }

    public function scopeWithActiveReports($query)
    {
        return $query->whereHas('siteReports', function ($q) {
            $q->where('status', 'Open');
        });
    }

    public function scopeWithoutActiveReports($query)
    {
        return $query->whereDoesntHave('siteReports', function ($q) {
            $q->where('status', 'Open');
        });
    }

    public function getCoordinatesAttribute()
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
        ];
    }

    public function getGoogleMapsUrlAttribute()
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function hasActiveReports()
    {
        return $this->siteReports()
            ->where('status', 'Open')
            ->exists();
    }

    public function getActiveReportsCount()
    {
        return $this->siteReports()
            ->where('status', 'Open')
            ->count();
    }

    public function getClosedReportsCount()
    {
        return $this->siteReports()
            ->where('status', 'Close')
            ->count();
    }

    public function getTotalReportsCount()
    {
        return $this->siteReports()->count();
    }
}