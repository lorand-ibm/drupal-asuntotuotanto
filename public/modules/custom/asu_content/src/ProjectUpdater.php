<?php

namespace Drupal\asu_content;

use Drupal\asu_content\Entity\Project;
use Psr\Log\LoggerInterface;

/**
 * Class ProjectUpdater.
 *
 * Update nodes based on the datetimes.
 */
class ProjectUpdater {

  private const APPLICATION_START = 'field_application_start_time';
  private const APPLICATION_END = 'field_application_end_time';
  private const APARTMENT_APPLICATION_TARGET_STATE = 'open_for_applications';
  private const APARTMENT_RESERVED_TARGET_STATE = 'reserved';
  private const APARTMENT_RESERVED_HASO_TARGET_STATE = 'reserved_haso';

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * Constructor.
   */
  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * Update Apartments's state of sale based on Project's schedule.
   *
   * @param Drupal\asu_content\Entity\Project $node
   *   Project node.
   *
   * @return int|mixed|string|null
   *   Project id.
   *
   * @throws \Exception
   */
  public function updateApartmentStateByApplicationTime(Project $project) {
    if (!isset($project->{self::APPLICATION_START})) {
      $message = 'Application start time field is required';
      throw new \InvalidArgumentException($message);
    }

    if (!isset($project->{self::APPLICATION_END})) {
      $message = 'Application end time field is required';
      throw new \InvalidArgumentException($message);
    }

    if ($project->isApplicationPeriod() &&
      !$this->isProjectSetOpenForApplications($project)) {
      return $this->updateApartmentsOpenForApplication($project);
    }

    if ($project->isApplicationPeriod('after') &&
        !$this->isProjectSetReserved($project)) {
      return $this->updateApartmentsReserved($project);
    }
  }

  /**
   * Update apartments state of sale.
   *
   * @param Drupal\asu_content\Entity\Project $node
   *   Project node.
   *
   * @return int|mixed|string|null
   *   Project id.
   */
  private function updateApartmentsOpenForApplication(Project $node) {
    $apartments = $node->getApartmentEntities();
    foreach ($apartments as $apartment) {
      $apartment->field_apartment_state_of_sale = self::APARTMENT_APPLICATION_TARGET_STATE;
      $apartment->save();
    }
    return $node->id();
  }

  /**
   * Update project apartments to new state after application period.
   *
   * @param Drupal\asu_content\Entity\Project $node
   *   Project node.
   *
   * @return int|mixed|string|null
   *   Project id.
   */
  private function updateApartmentsReserved(Project $node) {
    $apartments = $node->getApartmentEntities();
    foreach ($apartments as $apartment) {
      $apartment->field_apartment_state_of_sale = self::APARTMENT_RESERVED_TARGET_STATE;
      $apartment->save();
    }
    return $node->id();
  }

  /**
   * Check if the application time in progress.
   *
   * @param Drupal\asu_content\Entity\Project $node
   *   Project node.
   *
   * @return bool
   *   Check if the project is accepting applications.
   *
   * @throws \Exception
   */
  private function isProjectWithinApplicationPeriod(Project $node) {

  }

  /**
   * Check if project apartments already set open for applications.
   *
   * @param Drupal\asu_content\Entity\Project $node
   *   Project node.
   *
   * @return bool
   *   Has projects apartments already set as open to applications.
   *
   * @throws \Exception
   */
  private function isProjectSetOpenForApplications(Project $node): bool {
    $apartments = $node->getApartmentEntities();
    if (!$apartment = reset($apartments)) {
      throw new \Exception('Project has no apartments.');
    }
    if ($apartment->field_apartment_state_of_sale->target_id === self::APARTMENT_APPLICATION_TARGET_STATE) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Check if application end date is in the past.
   *
   * @param Drupal\asu_content\Entity\Project $node
   *   Project node.
   *
   * @return bool
   *   Has application time ended.
   *
   * @throws \Exception
   */
  private function applicationTimeOver(Project $node): bool {

  }

  /**
   * Check if project's apartments already been updated.
   *
   * @param Drupal\asu_content\Entity\Project $node
   *   Project node.
   *
   * @return bool
   *   Has project been updated.
   *
   * @throws \Exception
   */
  private function isProjectSetReserved(Project $node) {
    $apartments = $node->field_apartments->referencedEntities();
    if (!$apartment = reset($apartments)) {
      throw new \Exception('Project has no apartments.');
    }
    if ($apartment->field_apartment_state_of_sale->target_id === self::APARTMENT_RESERVED_TARGET_STATE ||
       $apartment->field_apartment_state_of_sale->target_id === self::APARTMENT_RESERVED_HASO_TARGET_STATE) {
      return TRUE;
    }
    return FALSE;
  }

}
