import { IsString, IsNotEmpty, IsEnum, IsOptional, IsNumber, IsDateString, MinLength } from "class-validator";
import { TradeCategory, JobPostStatus } from "@bildfie/db";

export class UpdateJobPostDto {
  @IsOptional()
  @IsString()
  @MinLength(3)
  title?: string;

  @IsOptional()
  @IsString()
  @MinLength(10)
  description?: string;

  @IsOptional()
  @IsEnum(TradeCategory)
  category?: TradeCategory;

  @IsOptional()
  @IsString()
  @IsNotEmpty()
  location?: string;

  @IsOptional()
  @IsNumber()
  budgetMin?: number;

  @IsOptional()
  @IsNumber()
  budgetMax?: number;

  @IsOptional()
  @IsDateString()
  dueDate?: string;

  @IsOptional()
  @IsEnum(JobPostStatus)
  status?: JobPostStatus;
}
