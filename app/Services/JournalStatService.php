<?php

	namespace App\Services;

	use App\Journal;
	use Carbon\Carbon;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class JournalStatService {

		/**
		 * Получение статистики по выдаче книг за период
		 *
		 * @param Carbon $dateFrom
		 * @param Carbon $dateTo
		 * @return array
		 */
		public function getPeriodStatistic(Carbon $dateFrom, Carbon $dateTo): array {
			$result = [];

			$books = $this->getBookList();
			$years = $this->getAnnualTotals($dateFrom, $dateTo);
			$months = $this->getMonthlyTotals($dateFrom, $dateTo);
			$stat = $this->getBookMonthlyTotals($dateFrom, $dateTo);

			// Цикл по годам
			foreach ($years as $year => $yearTotal) {
				$forIndex = $dateFrom->year == $year ? $dateFrom->month : 1;

				// Цикл по месяцам
				for ($i = $forIndex; $i <= 12; $i++) {
					if ($year == $dateTo->year && $i > $dateTo->month) {
						break;
					}

					$curDate = $year . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);

					// Цикл по книгам
					foreach ($books as $bookId => $book) {
						$bookTotal = [
							'value' => isset($stat[$curDate]) && isset($stat[$curDate][$bookId]) ?
								$stat[$curDate][$bookId] :
								0,
							'title' => $book,
							'date'  => $curDate,
						];

						array_push($result, $bookTotal);
					}

					$monthTotal = [
						'value' => $months->get($curDate)->value ?? 0,
						'date'  => $curDate,
					];

					array_push($result, $monthTotal);
				}

				array_push($result, (array)$yearTotal);
			}

			array_push($result, ['value' => Journal::count()]);

			return $result;
		}

		/**
		 * Получение всех книг библиотеки
		 *
		 * @return Collection
		 */
		private function getBookList(): Collection {
			return DB::table('books')->pluck('title', 'id');
		}

		/**
		 * Получение общего количества выдач книг по годам
		 *
		 * @param Carbon $dateFrom
		 * @param Carbon $dateTo
		 * @return Collection
		 */
		private function getAnnualTotals(Carbon $dateFrom, Carbon $dateTo): Collection {
			return DB::table('journal')
				->select(DB::raw('count(*) as value, YEAR(created_at) as date'))
				->whereBetween('created_at', [$dateFrom->copy()->startOfYear(), $dateTo])
				->groupBy('date')
				->get()->keyBy('date');
		}

		/**
		 * Получение общего количества выдач книг по месяцам
		 *
		 * @param Carbon $dateFrom
		 * @param Carbon $dateTo
		 * @return Collection
		 */
		private function getMonthlyTotals(Carbon $dateFrom, Carbon $dateTo):  Collection {
			return DB::table('journal')
				->select(DB::raw('count(*) as value, DATE_FORMAT(created_at, "%Y-%m") as date'))
				->whereBetween('created_at', [$dateFrom, $dateTo])
				->groupBy('date')
				->get()->keyBy('date');
		}

		/**
		 * Получение количества выдач каждой книги по месяцам
		 *
		 * @param Carbon $dateFrom
		 * @param Carbon $dateTo
		 * @return Collection
		 */
		private function getBookMonthlyTotals(Carbon $dateFrom, Carbon $dateTo): Collection {
			return DB::table('journal')
				->select(DB::raw('book_id, DATE_FORMAT(created_at, "%Y-%m") as date'))
				->whereBetween('created_at', [$dateFrom, $dateTo])
				->get()
				->groupBy('date')
				->map(function (Collection $collection) {
					return $collection
						->groupBy('book_id')
						->map(function (Collection $record) {
							return $record->count();
						});
				});
		}
	}