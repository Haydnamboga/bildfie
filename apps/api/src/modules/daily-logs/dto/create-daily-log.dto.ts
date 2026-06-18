import { IsString, IsNotEmpty, IsOptional, IsInt, Min, IsDateString } from "class-validator";

export class CreateDailyLogDto {
  @IsDateString() logDate: string;
  @IsOptional() @IsString() weather?: string;
  @IsOptional() @IsInt() @Min(0) workersCount?: number;
  @IsString() @IsNotEmpty() notes: string;
}
