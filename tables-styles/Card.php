<?php


namespace BookmePricingTables\TableStyles\Card;


class Card {
	public function createCard($service)
	{
		$content = '';
		$content .= '<div class="card mb-4 box-shadow">
          <div class="card-header">
            <h4 class="my-0 font-weight-normal">Free</h4>
          </div>
          <div class="card-body">
            <p class="h1 card-title pricing-card-title">' . $service->price . ' <small class="text-muted">/ ' . $service->duration . '</small></p>
            <a href="/reserveren" class="btn btn-lg btn-block btn-outline-primary">Nu reserveren</a>
          </div>
        </div>';
		return $content;
	}
}